<?php

namespace eLife\Annotations\Serializer;

use eLife\ApiSdk\ApiSdk;
use eLife\HypothesisClient\Model\Annotation;
use League\CommonMark\Block\Element;
use League\CommonMark\DocParser;
use League\CommonMark\ElementRendererInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class HypothesisClientAnnotationNormalizer implements NormalizerInterface
{
    const CANNOT_RENDER_CONTENT_COPY = 'NOTE: It is not possible to display this content.';
    const UNAVAILABLE_CONTENT_COPY = 'NOTE: There is no content available to display.';

    private $docParser;
    private $htmlRenderer;
    private $logger;

    public function __construct(DocParser $docParser, ElementRendererInterface $htmlRenderer, LoggerInterface $logger)
    {
        $this->docParser = $docParser;
        $this->htmlRenderer = $htmlRenderer;

        $this->logger = $logger;
    }

    /**
     * @param Annotation $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $created = $object->getCreatedDate()->format(ApiSdk::DATE_FORMAT);
        $updated = $object->getUpdatedDate()->format(ApiSdk::DATE_FORMAT);

        $data = [
            'id' => $object->getId(),
            'access' => (Annotation::PUBLIC_GROUP === $object->getPermissions()->getRead()) ? 'public' : 'restricted',
            'created' => $created,
            'document' => [
                'title' => $object->getDocument()->getTitle(),
                'uri' => $object->getUri(),
            ],
        ];

        if ($object->getReferences()) {
            $data['ancestors'] = $object->getReferences();
        }

        if ($object->getText()) {
            $data['content'] = $this->processText($object->getText());
        }

        if ($created !== $updated) {
            $data['updated'] = $updated;
        }

        if ($object->getTarget()->getSelector()) {
            $data['highlight'] = $object->getTarget()->getSelector()->getTextQuote()->getExact();
        }

        if (empty($data['highlight']) && empty($data['content'])) {
            $this->logger->warning(sprintf('Annotation detected without highlight or content (ID: %s)', $data['id']), ['annotation' => $data]);
            $data['content'] = [
                [
                    'type' => 'paragraph',
                    'text' => self::UNAVAILABLE_CONTENT_COPY,
                ],
            ];
        }

        return $data;
    }

    private function processText(string $text) : array
    {
        $blocks = $this->docParser->parse($text)->children();
        $data = [];

        foreach ($blocks as $block) {
            switch (true) {
                case $block instanceof Element\ThematicBreak:
                    break;
                case $block instanceof Element\ListBlock:
                    $data[] = $this->processListBlock($block);
                    break;
                case $block instanceof Element\BlockQuote:
                    if ($rendered = $this->htmlRenderer->renderBlock($block)) {
                        $data[] = [
                            'type' => 'quote',
                            'text' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => $rendered,
                                ],
                            ],
                        ];
                    }
                    break;
                case $block instanceof Element\HtmlBlock:
                case $block instanceof Element\Paragraph:
                    if ($rendered = $this->htmlRenderer->renderBlock($block)) {
                        $data[] = [
                            'type' => 'paragraph',
                            'text' => $rendered,
                        ];
                    }
                    break;
                case $block instanceof Element\FencedCode:
                case $block instanceof Element\IndentedCode:
                    if ($rendered = $this->htmlRenderer->renderBlock($block)) {
                        $data[] = [
                            'type' => 'code',
                            'code' => $rendered,
                        ];
                    }
                    break;
            }
        }

        if (empty($data)) {
            $data = [
                [
                    'type' => 'paragraph',
                    'text' => self::CANNOT_RENDER_CONTENT_COPY,
                ],
            ];
        }

        return $data;
    }

    private function processListBlock(Element\ListBlock $block)
    {
        $gather = function (Element\ListBlock $list) use (&$gather, &$render) {
            $items = [];
            foreach ($list->children() as $item) {
                foreach ($item->children() as $child) {
                    if ($child instanceof Element\ListBlock) {
                        $items[] = [$render($child)];
                    } elseif ($item = $this->htmlRenderer->renderBlock($child)) {
                        $items[] = $item;
                    }
                }
            }

            return $items;
        };

        $render = function (Element\ListBlock $list) use ($gather) {
            return [
                'type' => 'list',
                'prefix' => (Element\ListBlock::TYPE_ORDERED === $list->getListData()->type) ? 'number' : 'bullet',
                'items' => $gather($list),
            ];
        };

        return $render($block);
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Annotation;
    }
}

<?php

namespace eLife\Annotations\Serializer;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Block;
use eLife\HypothesisClient\Model\Annotation;
use League\CommonMark\Block\Element;
use League\CommonMark\Environment;
use League\CommonMark\HtmlRenderer;
use League\CommonMark\Inline\Element\HtmlInline;
use League\CommonMark\Inline\Element\Image;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AnnotationNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    const CANNOT_RENDER_CONTENT_COPY = 'NOTE: It is not possible to display this content.';

    use NormalizerAwareTrait;

    private $docParser;
    private $htmlRenderer;

    public function __construct()
    {
        $environment = Environment::createCommonMarkEnvironment();

        $environment->addBlockParser(new CommonMark\Block\Parser\LatexParser());
        $environment->addBlockParser(new CommonMark\Block\Parser\MathMLParser());

        $environment->addBlockRenderer(CommonMark\Block\Element\Latex::class, new CommonMark\Block\Renderer\LatexRenderer());
        $environment->addBlockRenderer(CommonMark\Block\Element\MathML::class, new CommonMark\Block\Renderer\MathMLRenderer());
        $environment->addBlockRenderer(Element\BlockQuote::class, new CommonMark\Block\Renderer\BlockQuoteRenderer());
        $environment->addBlockRenderer(Element\FencedCode::class, new CommonMark\Block\Renderer\CodeRenderer());
        $environment->addBlockRenderer(Element\HtmlBlock::class, new CommonMark\Block\Renderer\HtmlBlockRenderer());
        $environment->addBlockRenderer(Element\IndentedCode::class, new CommonMark\Block\Renderer\CodeRenderer());
        $environment->addBlockRenderer(Element\ListItem::class, new CommonMark\Block\Renderer\ListItemRenderer());
        $environment->addBlockRenderer(Element\Paragraph::class, new CommonMark\Block\Renderer\ParagraphRenderer());

        $environment->addInlineRenderer(HtmlInline::class, new CommonMark\Inline\Renderer\HtmlInlineRenderer());
        $environment->addInlineRenderer(Image::class, new CommonMark\Inline\Renderer\ImageRenderer());

        $this->docParser = new CommonMark\DocParser($environment);
        $this->htmlRenderer = new HtmlRenderer($environment);
    }

    /**
     * @param Annotation $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $content = $object->getText() ? $this->processText($object->getText()) : null;

        $data = array_filter([
            'id' => $object->getId(),
            'access' => ($object->getPermissions()->getRead() === Annotation::PUBLIC_GROUP) ? 'public' : 'restricted',
            'content' => $content,
            'parents' => $object->getReferences(),
            'created' => $object->getCreatedDate()->format(ApiSdk::DATE_FORMAT),
            'updated' => $object->getUpdatedDate()->format(ApiSdk::DATE_FORMAT),
            'document' => [
                'title' => $object->getDocument()->getTitle(),
                'uri' => $object->getUri(),
            ],
        ]) + ['parents' => []];
        if ($data['created'] === $data['updated']) {
            unset($data['updated']);
        }
        if ($object->getTarget()->getSelector()) {
            $data['highlight'] = $object->getTarget()->getSelector()->getTextQuote()->getExact();
        }
        if (empty($data['highlight']) && empty($data['content'])) {
            $data['content'] = self::CANNOT_RENDER_CONTENT_COPY;
        }

        return $data;
    }

    private function processText(string $text) : array
    {
        $blocks = $this->docParser->parse($text)->children();
        $data = [];

        foreach ($blocks as $block) {
            $rendered = $this->htmlRenderer->renderBlock($block);
            if (empty($rendered)) {
                continue;
            }

            switch (true) {
                case $block instanceof Element\ThematicBreak:
                    break;
                case $block instanceof Element\ListBlock:
                    $data[] = $this->processListBlock($block);
                    break;
                case $block instanceof Element\BlockQuote:
                    $data[] = new Block\Quote([new Block\Paragraph($rendered)]);
                    break;
                case $block instanceof Element\HtmlBlock:
                case $block instanceof CommonMark\Block\Element\Latex:
                case $block instanceof CommonMark\Block\Element\MathML:
                case $block instanceof Element\Paragraph:
                    $data[] = new Block\Paragraph($rendered);
                    break;
                case $block instanceof Element\FencedCode:
                case $block instanceof Element\IndentedCode:
                    $data[] = new Block\Code($rendered);
                    break;
            }
        }

        if (empty($data)) {
            $data = [new Block\Paragraph(self::CANNOT_RENDER_CONTENT_COPY)];
        }

        return array_map(function (Block $block) {
            return $this->normalizer->normalize($block);
        }, $data);
    }

    private function processListBlock(Element\ListBlock $block)
    {
        $gather = function (Element\ListBlock $list) use (&$gather, &$render) {
            $items = [];
            foreach ($list->children() as $item) {
                foreach ($item->children() as $child) {
                    if ($child instanceof Element\ListBlock) {
                        $items[] = new ArraySequence([$render($child)]);
                    } else {
                        $items[] = $this->htmlRenderer->renderBlock($child);
                    }
                }
            }

            return $items;
        };

        $render = function (Element\ListBlock $list) use ($gather) {
            return new Block\Listing(
                (Element\ListBlock::TYPE_ORDERED === $list->getListData()->type) ? Block\Listing::PREFIX_NUMBER : Block\Listing::PREFIX_BULLET,
                new ArraySequence($gather($list))
            );
        };

        return $render($block);
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Annotation;
    }
}

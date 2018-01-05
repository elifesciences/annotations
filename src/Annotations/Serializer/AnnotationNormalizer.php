<?php

namespace eLife\Annotations\Serializer;

use eLife\Annotations\Renderer;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Block;
use eLife\HypothesisClient\Model\Annotation;
use League\CommonMark\Block\Element;
use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use League\CommonMark\HtmlRenderer;
use League\CommonMark\Inline\Renderer\HtmlInlineRenderer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AnnotationNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private $docParser;
    private $htmlRenderer;

    public function __construct()
    {
        $environment = Environment::createCommonMarkEnvironment();

        $environment->addBlockRenderer('League\CommonMark\Block\Element\BlockQuote', new Renderer\Block\BlockQuoteRenderer());
        $environment->addBlockRenderer('League\CommonMark\Block\Element\FencedCode', new Renderer\Block\CodeRenderer());
        $environment->addBlockRenderer('League\CommonMark\Block\Element\HtmlBlock', new Renderer\Block\HtmlBlockRenderer());
        $environment->addBlockRenderer('League\CommonMark\Block\Element\IndentedCode', new Renderer\Block\CodeRenderer());
        $environment->addBlockRenderer('League\CommonMark\Block\Element\ListItem', new Renderer\Block\ListItemRenderer());
        $environment->addBlockRenderer('League\CommonMark\Block\Element\Paragraph', new Renderer\Block\ParagraphRenderer());

        $environment->addInlineRenderer('League\CommonMark\Inline\Element\HtmlInline', new HtmlInlineRenderer());
        $environment->addInlineRenderer('League\CommonMark\Inline\Element\Image', new Renderer\Inline\ImageRenderer());

        $this->docParser = new DocParser($environment);
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
            $data['content'] = [$this->normalizer->normalize(new Block\Paragraph('**empty**'))];
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
                    $data[] = new Block\Listing(
                        (Element\ListBlock::TYPE_ORDERED === $block->getListData()->type) ? Block\Listing::PREFIX_NUMBER : Block\Listing::PREFIX_BULLET,
                        new ArraySequence(array_map(function (Element\ListItem $item) {
                            return $this->htmlRenderer->renderBlock($item);
                        }, $block->children()))
                    );
                    break;
                case $block instanceof Element\BlockQuote:
                    $data[] = new Block\Quote([new Block\Paragraph($rendered)]);
                    break;
                case $block instanceof Element\HtmlBlock:
                case $block instanceof Element\Paragraph:
                    $data[] = new Block\Paragraph($rendered);
                    break;
                case $block instanceof Element\FencedCode:
                case $block instanceof Element\IndentedCode:
                    $data[] = new Block\Code($rendered);
                    break;
            }
        }

        return array_map(function (Block $block) {
            return $this->normalizer->normalize($block);
        }, $data);
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Annotation;
    }
}

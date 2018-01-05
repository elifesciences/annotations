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
        $environment->addBlockRenderer('League\CommonMark\Block\Element\ListItem', new Renderer\Block\ListItemRenderer());
        $environment->addBlockRenderer('League\CommonMark\Block\Element\Paragraph', new Renderer\Block\ParagraphRenderer());
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

        return $data;
    }

    private function processText(string $text) : array
    {
        $blocks = $this->docParser->parse($text)->children();
        $data = [];

        foreach ($blocks as $block) {
            if ($block instanceof Element\ListBlock) {
                $data[] = new Block\Listing(
                    (Element\ListBlock::TYPE_ORDERED === $block->getListData()->type) ? Block\Listing::PREFIX_NUMBER : Block\Listing::PREFIX_BULLET,
                    new ArraySequence(array_map(function (Element\ListItem $item) {
                        return $this->htmlRenderer->renderBlock($item);
                    }, $block->children()))
                );
            } elseif ($block instanceof Element\BlockQuote) {
                $data[] = new Block\Quote([new Block\Paragraph($this->htmlRenderer->renderBlock($block))]);
            } elseif ($block instanceof Element\Paragraph) {
                $data[] = new Block\Paragraph($this->htmlRenderer->renderBlock($block));
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
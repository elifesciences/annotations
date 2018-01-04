<?php

namespace eLife\Annotations\Serializer;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Paragraph;
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
            if ($block instanceof Element\Paragraph) {
                $data[] = new Paragraph(preg_replace('~^<p>(.*)</p>$~', '$1', $this->htmlRenderer->renderBlock($block)));
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

<?php

namespace eLife\Annotations\Serializer;

use eLife\ApiSdk\ApiSdk;
use eLife\HypothesisClient\Model\Annotation;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AnnotationNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

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
            'updated' => $object->getCreatedDate()->format(ApiSdk::DATE_FORMAT),
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
        // @todo - split content into appropriate blocks.
        return [['type' => 'paragraph', 'text' => $text]];
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Annotation;
    }
}

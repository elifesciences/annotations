<?php

namespace eLife\HypothesisClient\ApiSdk\Serializer;

use eLife\HypothesisClient\ApiSdk\Model\Annotation;
use eLife\HypothesisClient\ApiSdk\Model\Links;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AnnotationNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Annotation
    {
        return new Annotation(
            $data['id'],
            new Links($data['links']['incontext'], $data['links']['json'] ?? null, $data['links']['html'] ?? null),
            $data['text'] ?? null
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Annotation::class === $type;
    }

    /**
     * @param Annotation $object
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'id' => $object->getId(),
            'links' => array_filter([
                'incontext' => $object->getLinks()->getIncontext(),
                'json' => $object->getLinks()->getJson(),
                'html' => $object->getLinks()->getHtml(),
            ]),
            'text' => $object->getText(),
        ];

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Annotation;
    }
}

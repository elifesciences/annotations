<?php

namespace eLife\HypothesisClient\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\HypothesisClient\ApiSdk\ApiSdk;
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
            DateTimeImmutable::createFromFormat(ApiSdk::DATE_FORMAT, $data['created'])->setTimezone(new DateTimeZone('Z')),
            !empty($data['updated']) ? DateTimeImmutable::createFromFormat(ApiSdk::DATE_FORMAT, $data['updated'])->setTimezone(new DateTimeZone('Z')) : null,
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
            'created' => $object->getPublishedDate()->format(ApiSdk::DATE_FORMAT),
            'links' => array_filter([
                'incontext' => $object->getLinks()->getIncontext(),
                'json' => $object->getLinks()->getJson(),
                'html' => $object->getLinks()->getHtml(),
            ]),
        ];

        if ($object->getUpdatedDate()) {
            $data['updated'] = $object->getUpdatedDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($object->getText()) {
            $data['text'] = $object->getText();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Annotation;
    }
}

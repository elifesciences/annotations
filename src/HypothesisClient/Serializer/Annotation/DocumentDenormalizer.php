<?php

namespace eLife\HypothesisClient\Serializer\Annotation;

use eLife\HypothesisClient\Model\Annotation\Document;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class DocumentDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    const NO_TITLE_AVAILABLE_COPY = 'No title available';

    public function denormalize($data, $class, $format = null, array $context = []) : Document
    {
        return new Document($data['title'][0] ?? self::NO_TITLE_AVAILABLE_COPY);
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Document::class === $type;
    }
}

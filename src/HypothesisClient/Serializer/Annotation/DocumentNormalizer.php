<?php

namespace eLife\HypothesisClient\Serializer\Annotation;

use eLife\HypothesisClient\Model\Annotation\Document;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class DocumentNormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Document
    {
        // @todo - should this be optional?
        return new Document($data['title'][0] ?? 'Unknown');
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Document::class === $type;
    }
}

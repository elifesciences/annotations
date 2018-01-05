<?php

namespace eLife\HypothesisClient\Serializer\Annotation\Target\Selector;

use eLife\HypothesisClient\Model\Annotation\Target\Selector\Range;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class RangeDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Range
    {
        return new Range($data['startContainer'], $data['endContainer'], $data['startOffset'], $data['endOffset']);
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Range::class === $type;
    }
}

<?php

namespace eLife\HypothesisClient\Serializer\Annotation\Target\Selector;

use eLife\HypothesisClient\Model\Annotation\Target\Selector\TextPosition;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class TextPositionNormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : TextPosition
    {
        return new TextPosition($data['start'], $data['end']);
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return TextPosition::class === $type;
    }
}

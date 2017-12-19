<?php

namespace eLife\HypothesisClient\Serializer\Annotation\Target\Selector;

use eLife\HypothesisClient\Model\Annotation\Target\Selector\TextQuote;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class TextQuoteNormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : TextQuote
    {
        return new TextQuote($data['exact'], $data['prefix'], $data['suffix']);
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return TextQuote::class === $type;
    }
}

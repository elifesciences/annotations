<?php

namespace eLife\HypothesisClient\Serializer\Annotation\Target;

use eLife\HypothesisClient\Model\Annotation\Target\Selector;
use eLife\HypothesisClient\Model\Annotation\Target\Selector\TextQuote;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class SelectorDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Selector
    {
        $selectors = [];
        foreach ($data as $selector) {
            switch ($selector['type']) {
                case 'TextQuoteSelector':
                    $selectors['textQuote'] = $this->denormalizer->denormalize($selector, TextQuote::class);
                    break;
                default:
                    continue;
            }
        }

        return new Selector($selectors['textQuote']);
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Selector::class === $type;
    }
}

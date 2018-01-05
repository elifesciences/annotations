<?php

namespace eLife\HypothesisClient\Serializer\Annotation\Target;

use eLife\HypothesisClient\Model\Annotation\Target\Selector;
use eLife\HypothesisClient\Model\Annotation\Target\Selector\Fragment;
use eLife\HypothesisClient\Model\Annotation\Target\Selector\Range;
use eLife\HypothesisClient\Model\Annotation\Target\Selector\TextPosition;
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
                case 'FragmentSelector':
                    if (!empty($selector['conformsTo']) && !empty($selector['value'])) {
                        $selectors['fragment'] = $this->denormalizer->denormalize($selector, Fragment::class);
                    }
                    break;
                case 'RangeSelector':
                    $selectors['range'] = $this->denormalizer->denormalize($selector, Range::class);
                    break;
                case 'TextPositionSelector':
                    $selectors['textPosition'] = $this->denormalizer->denormalize($selector, TextPosition::class);
                    break;
                case 'TextQuoteSelector':
                    $selectors['textQuote'] = $this->denormalizer->denormalize($selector, TextQuote::class);
                    break;
            }
        }

        return new Selector($selectors['textQuote'], $selectors['textPosition'] ?? null, $selectors['range'] ?? null, $selectors['fragment'] ?? null);
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Selector::class === $type;
    }
}

<?php

namespace eLife\HypothesisClient\Serializer\Annotation;

use eLife\HypothesisClient\Model\Annotation\Target;
use eLife\HypothesisClient\Model\Annotation\Target\Selector;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class TargetNormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Target
    {
        if (!empty($data['selector'])) {
            $data['selector'] = $this->denormalizer->denormalize($data['selector'], Selector::class);
        } else {
            $data['selector'] = null;
        }

        return new Target($data['source'], $data['selector']);
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Target::class === $type;
    }
}

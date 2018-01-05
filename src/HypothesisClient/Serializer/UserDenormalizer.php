<?php

namespace eLife\HypothesisClient\Serializer;

use eLife\HypothesisClient\Model\User;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class UserDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : User
    {
        return new User($data['username'], $data['email'] ?? null, $data['display_name'] ?? null, $data['new'] ?? false);
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return User::class === $type;
    }
}

<?php

namespace eLife\HypothesisClient\Serializer;

use eLife\HypothesisClient\Model\Token;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class TokenDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Token
    {
        return new Token($data['access_token'], $data['token_type'], $data['expires_in'], $data['refresh_token']);
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Token::class === $type;
    }
}

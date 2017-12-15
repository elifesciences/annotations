<?php

namespace eLife\HypothesisClient\Client;

use eLife\HypothesisClient\ApiClient\TokenClient;
use eLife\HypothesisClient\Model\Token as ModelToken;
use eLife\HypothesisClient\Result\Result;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Token
{
    private $normalizer;
    private $tokenClient;

    public function __construct(TokenClient $tokenClient, DenormalizerInterface $normalizer)
    {
        $this->tokenClient = $tokenClient;
        $this->normalizer = $normalizer;
    }

    public function get(string $username) : PromiseInterface
    {
        return $this->tokenClient
            ->getToken(
                [],
                $username
            )
            ->then(function (Result $result) {
                $tmp = $this->normalizer->denormalize($result->toArray(), ModelToken::class);
                return $this->normalizer->denormalize($result->toArray(), ModelToken::class);
            });
    }
}

<?php

namespace eLife\HypothesisClient\Client;

use eLife\HypothesisClient\ApiClient\TokenClient;
use eLife\HypothesisClient\Model\Token as ModelToken;
use eLife\HypothesisClient\Result\Result;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Token
{
    private $serializer;
    private $tokenClient;

    public function __construct(TokenClient $tokenClient, DenormalizerInterface $serializer)
    {
        $this->tokenClient = $tokenClient;
        $this->serializer = $serializer;
    }

    public function get(string $username) : PromiseInterface
    {
        return $this->tokenClient
            ->getToken(
                [],
                $username
            )
            ->then(function (Result $result) {
                return $this->serializer->denormalize($result->toArray(), ModelToken::class);
            });
    }
}

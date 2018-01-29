<?php

namespace eLife\HypothesisClient\Client;

use eLife\HypothesisClient\ApiClient\TokenClient;
use eLife\HypothesisClient\Model\Token as ModelToken;
use eLife\HypothesisClient\Result\Result;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

final class Token
{
    private $serializer;
    private $tokenClient;
    private $cache;

    public function __construct(TokenClient $tokenClient, DenormalizerInterface $serializer, CacheInterface $cache = null)
    {
        $this->tokenClient = $tokenClient;
        $this->serializer = $serializer;
        $this->cache = $cache;
    }

    public function get(string $username) : PromiseInterface
    {
        $cacheKey = "hypothesis.token.{$username}";

        if ($this->cache && $token = $this->cache->get($cacheKey)) {
            if ($token instanceof ModelToken) {
                return promise_for($token);
            }
        }

        $token = $this->tokenClient
            ->getToken(
                [],
                $username
            )
            ->then(function (Result $result) {
                return $this->serializer->denormalize($result->toArray(), ModelToken::class);
            });

        if ($this->cache) {
            $token = $token
                ->then(function (ModelToken $token) use ($cacheKey) {
                    $this->cache->set($cacheKey, $token, (int) $token->getExpiresIn());

                    return $token;
                });
        }

        return $token;
    }
}

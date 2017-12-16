<?php

namespace eLife\HypothesisClient\Client;

use eLife\HypothesisClient\ApiClient\SearchClient;
use eLife\HypothesisClient\Model\Annotation;
use eLife\HypothesisClient\Result\Result;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Search
{
    private $count;
    private $normalizer;
    private $searchClient;

    public function __construct(SearchClient $searchClient, DenormalizerInterface $normalizer)
    {
        $this->searchClient = $searchClient;
        $this->normalizer = $normalizer;
    }

    public function query(string $username = null, string $accessToken = null, int $offset = 5, int $limit = 20, bool $descendingOrder = true) : PromiseInterface
    {
        return $this->searchClient
            ->query(
                [],
                $username,
                $accessToken,
                $offset,
                $limit,
                $descendingOrder
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];
                return $result;
            })
            ->then(function (Result $result) {
                return array_map(function (array $annotation) {
                    return $this->normalizer->denormalize($annotation, Annotation::class);
                }, $result['rows']);
            });
    }

    public function count() : int
    {
        return $this->count ?? 0;
    }
}

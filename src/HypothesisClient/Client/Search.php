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
    private $serializer;
    private $searchClient;

    public function __construct(SearchClient $searchClient, DenormalizerInterface $serializer)
    {
        $this->searchClient = $searchClient;
        $this->serializer = $serializer;
    }

    public function query(
        string $username = null,
        string $accessToken = null,
        int $offset = 5,
        int $limit = 20,
        bool $descendingOrder = true,
        bool $updatedSortBy = true
    ) : PromiseInterface {
        return $this->searchClient
            ->query(
                [],
                $username,
                $accessToken,
                $offset,
                $limit,
                $descendingOrder,
                $updatedSortBy
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];
                return $result;
            })
            ->then(function (Result $result) {
                return array_map(function (array $annotation) {
                    return $this->serializer->denormalize($annotation, Annotation::class);
                }, $result['rows']);
            });
    }

    public function count() : int
    {
        return $this->count ?? 0;
    }
}

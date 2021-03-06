<?php

namespace eLife\HypothesisClient\Client;

use eLife\HypothesisClient\ApiClient\SearchClient;
use eLife\HypothesisClient\Model\Annotation;
use eLife\HypothesisClient\Model\SearchResults;
use eLife\HypothesisClient\Result\Result;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Search
{
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
        int $offset = 0,
        int $limit = 20,
        bool $descendingOrder = true,
        string $sort = 'updated'
    ) : PromiseInterface {
        return $this->searchClient
            ->query(
                [],
                $username,
                $accessToken,
                $offset,
                $limit,
                $descendingOrder,
                $sort
            )
            ->then(function (Result $result) {
                return new SearchResults(
                    $result['total'],
                    array_map(function (array $annotation) {
                        return $this->serializer->denormalize($annotation, Annotation::class);
                    }, $result['rows'])
                );
            });
    }
}

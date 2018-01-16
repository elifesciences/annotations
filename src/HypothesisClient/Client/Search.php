<?php

namespace eLife\HypothesisClient\Client;

use eLife\HypothesisClient\ApiClient\SearchClient;
use eLife\HypothesisClient\Model\Annotation;
use eLife\HypothesisClient\Result\Result;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Search
{
    private $total = [];
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
            ->then(function (Result $result) use ($username, $accessToken) {
                $this->total[$this->getTotalKey($username, $accessToken)] = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                return array_map(function (array $annotation) {
                    return $this->serializer->denormalize($annotation, Annotation::class);
                }, $result['rows']);
            });
    }

    public function count(
        string $username = null,
        string $accessToken = null
    ) : int {
        if (!$this->hasTotalKey($username, $accessToken)) {
            $this->query($username, $accessToken)->wait();
        }

        return $this->total[$this->getTotalKey($username, $accessToken)];
    }

    private function hasTotalKey(
        string $username = null,
        string $accessToken = null
    ) : bool {
        return isset($this->total[$this->getTotalKey($username, $accessToken)]);
    }

    private function getTotalKey(
        string $username = null,
        string $accessToken = null
    ) : string {
        $key = (string) $username.(string) $accessToken;

        return md5($key ?? Annotation::PUBLIC_GROUP);
    }
}

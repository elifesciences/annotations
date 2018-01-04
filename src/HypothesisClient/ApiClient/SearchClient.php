<?php

namespace eLife\HypothesisClient\ApiClient;

use eLife\HypothesisClient\HttpClient\HttpClient;
use eLife\HypothesisClient\HttpClient\UserAgentPrependingHttpClient;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Uri;
use function GuzzleHttp\Psr7\build_query;

final class SearchClient
{
    use ApiClient;

    private $group;

    public function __construct(HttpClient $httpClient, string $group = '__world__', array $headers = [])
    {
        $this->httpClient = new UserAgentPrependingHttpClient($httpClient, 'HypothesisClient');
        $this->group = $group;
        $this->headers = $headers;
    }

    public function query(
        array $headers,
        string $username = null,
        string $token = null,
        int $offset = 0,
        int $limit = 20,
        bool $descendingOrder = true,
        string $sort = 'updated'
    ) : PromiseInterface {
        $query = [];
        if ($username && false) {
            $query['user'] = $username;
        }
        $query += [
            'group' => $this->group,
            'offset' => $offset,
            'limit' => $limit,
            'order' => $descendingOrder ? 'desc' : 'asc',
            'sort' => $sort,
        ];

        return $this->getRequest(
            Uri::fromParts([
                'path' => 'search',
                'query' => build_query($query),
            ]),
            (($token) ? ['Authorization' => 'Bearer '.$token] : []) + $headers
        );
    }
}

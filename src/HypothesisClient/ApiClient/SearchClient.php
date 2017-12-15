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
        int $page = 1,
        int $perPage = 20,
        bool $descendingOrder = true
    ) : PromiseInterface {
        return $this->getRequest(
            Uri::fromParts([
                'path' => 'search',
                'query' => build_query([
                    'user' => $username,
                    'group' => $this->group,
                    'offset' => ($page - 1) * $perPage,
                    'limit' => $perPage,
                    'order' => $descendingOrder ? 'desc' : 'asc',
                ]),
            ]),
            (($token) ? ['Authorization' => 'Bearer '.$token] : []) + $headers
        );
    }
}

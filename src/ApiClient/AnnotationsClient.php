<?php

namespace eLife\HypothesisClient\ApiClient;

use eLife\HypothesisClient\ApiClient;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Uri;
use function GuzzleHttp\Psr7\build_query;

final class AnnotationsClient
{
    use ApiClient;

  /**
   * @param array $headers
   * @param string $user
   * @param int $page
   * @param int $perPage
   * @param bool $descendingOrder
   * @param string|null $group
   * @return \GuzzleHttp\Promise\PromiseInterface
   */
    public function listAnnotations(
        array $headers = [],
        string $user,
        int $page = 1,
        int $perPage = 20,
        bool $descendingOrder = true,
        $group = '__world__'
    ) : PromiseInterface {
        return $this->getRequest(
            Uri::fromParts([
                'path' => 'search',
                'query' => build_query(array_filter([
                    'user' => $user,
                    'group' => $group,
                    'offset' => ($page-1)*$perPage,
                    'limit' => $perPage,
                    'order' => $descendingOrder ? 'desc' : 'asc',
                ])),
            ]),
            $headers
        );
    }
}

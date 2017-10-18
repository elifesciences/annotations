<?php

namespace tests\eLife\HypothesisClient\ApiSdk;

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Middleware\MockMiddleware;
use eLife\HypothesisClient\HttpClient;
use eLife\HypothesisClient\HttpClient\Guzzle6HttpClient;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LogicException;

abstract class ApiTestCase extends TestCase
{
    /** @var InMemoryStorageAdapter */
    public $storage;

    /** @var HttpClient */
    private $httpClient;

    /**
     * @after
     */
    final public function resetMocks()
    {
        $this->httpClient = null;
    }

    final protected function getHttpClient() : HttpClient
    {
        if (null === $this->httpClient) {
            $this->storage = new InMemoryStorageAdapter();

            $stack = HandlerStack::create();
            $stack->push(new MockMiddleware($this->storage, 'replay'));

            $this->httpClient = new Guzzle6HttpClient(new Client([
                'base_uri' => 'https://hypothes.is/api',
                'handler' => $stack,
            ]));
        }

        return $this->httpClient;
    }

    final protected function mockNotFound(string $uri, array $headers = [])
    {
        $this->storage->save(
            new Request(
                'GET',
                'https://hypothes.is/'.$uri,
                $headers
            ),
            new Response(
                200,
                [],
                json_encode([
                    'total' => 6,
                    'rows' => [],
                ])
            )
        );
    }

    final protected function mockAnnotationsCall(string $user, string $group, int $page, int $perPage, int $total, $descendingOrder = true)
    {
        $annotations = array_map(function (int $id) {
            return $this->createAnnotationJson('annotation-'.$id);
        }, $this->generateIdList($page, $perPage, $total));

        $this->storage->save(
            new Request(
                'GET',
                'https://hypothes.is/api/search?user='.$user.'&group='.$group.'&offset='.(($page - 1) * $perPage).'&limit='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc')
            ),
            new Response(
                200,
                [],
                json_encode([
                    'total' => $total,
                    'rows' => $annotations,
                ])
            )
        );
    }

    private function generateIdList(int $page, int $perPage, int $total) : array
    {
        $firstId = ($page * $perPage) - $perPage + 1;
        if ($firstId > $total) {
            throw new LogicException('Page should not exist');
        }

        $lastId = $firstId + $perPage - 1;
        if ($lastId > $total) {
            $lastId = $total;
        }

        return range($firstId, $lastId);
    }

    final private function createAnnotationJson(string $id, bool $complete = false)
    {
        $annotation = [
            'id' => $id,
            'created' => '2017-09-15T14:40:52.317491+00:00',
            'links' => [
                'incontext' => 'http://url.incontext',
            ],
        ];

        if ($complete) {
            $annotation['updated'] = '2017-09-15T14:42:43.061419+00:00';
            $annotation['links'] += [
                'json' => 'http://url.json',
                'html' => 'http://url.html',
            ];
            $annotation['text'] = 'text';
        }

        return $annotation;
    }
}

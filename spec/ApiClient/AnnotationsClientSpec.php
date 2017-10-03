<?php

namespace spec\eLife\HypothesisClient\ApiClient;

use eLife\HypothesisClient\HttpClient;
use eLife\HypothesisClient\Result\ArrayResult;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;

final class AnnotationsClientSpec extends ObjectBehavior
{
    private $httpClient;

    public function let(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;

        $this->beConstructedWith($httpClient, ['X-Foo' => 'bar']);
    }

    public function it_lists_annotations()
    {
        $request = new Request('GET', 'api/search?user=list&group=__world__&offset=0&limit=20&order=desc',
            ['X-Foo' => 'bar', 'User-Agent' => 'HypothesisClient']);
        $response = new FulfilledPromise(new ArrayResult(['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->listAnnotations([], 'list', 1, 20, true, '__world__')
            ->shouldBeLike($response);
    }
}

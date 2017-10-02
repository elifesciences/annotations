<?php

namespace spec\eLife\HypothesisClient\HttpClient;

use eLife\HypothesisClient\HttpClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PhpSpec\ObjectBehavior;

final class Guzzle6HttpClientSpec extends ObjectBehavior
{
    private $mock;
    private $guzzle;

    public function let()
    {
        $this->mock = new MockHandler();
        $this->guzzle = new Client(['handler' => HandlerStack::create($this->mock)]);

        $this->beConstructedWith($this->guzzle);
    }

    public function it_is_a_http_client()
    {
        $this->shouldHaveType(HttpClient::class);
    }

    public function it_sends_requests()
    {
        $request = new Request('GET', 'foo');
        $response = new Response(200, [],
            json_encode(['foo' => ['bar', 'baz']]));

        $this->mock->append($response);

        $this->send($request)->shouldHaveType(PromiseInterface::class);
    }
}

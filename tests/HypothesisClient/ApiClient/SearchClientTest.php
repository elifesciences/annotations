<?php

namespace tests\eLife\HypothesisClient\HttpClient;

use eLife\HypothesisClient\ApiClient\SearchClient;
use eLife\HypothesisClient\ApiClient\UsersClient;
use eLife\HypothesisClient\HttpClient\HttpClient;
use eLife\HypothesisClient\Result\ArrayResult;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PHPUnit_Framework_TestCase;
use tests\eLife\HypothesisClient\RequestConstraint;
use TypeError;

/**
 * @covers \eLife\HypothesisClient\ApiClient\SearchClient
 */
final class SearchClientTest extends PHPUnit_Framework_TestCase
{
    private $httpClient;
    /** @var SearchClient */
    private $searchClient;

    /**
     * @before
     */
    protected function setUpClient()
    {
        $this->httpClient = $this->createMock(HttpClient::class);
        $this->searchClient = new SearchClient(
            $this->httpClient,
            'group',
            ['X-Foo' => 'bar']
        );
    }

    /**
     * @test
     */
    public function it_requires_a_http_client()
    {
        try {
            new UsersClient('foo');
            $this->fail('A HttpClient is required');
        } catch (TypeError $error) {
            $this->assertTrue(true, 'A HttpClient is required');
            $this->assertContains(
                'must implement interface '.HttpClient::class.', string given',
                $error->getMessage()
            );
        }
    }

    /**
     * @test
     */
    public function it_lists_annotations_public()
    {
        $request = new Request('GET', 'search?user=username&group=group&offset=0&limit=20&order=desc',
            ['X-Foo' => 'bar', 'User-Agent' => 'HypothesisClient']);
        $response = new FulfilledPromise(new ArrayResult(['foo' => ['bar', 'baz']]));
        $this->httpClient
            ->expects($this->once())
            ->method('send')
            ->with(RequestConstraint::equalTo($request))
            ->willReturn($response);
        $this->assertEquals($response, $this->searchClient->query([], 'username', null, 0, 20, true));
    }

    /**
     * @test
     */
    public function it_lists_annotations_restricted()
    {
        $request = new Request('GET', 'search?user=username&group=group&offset=0&limit=20&order=desc',
            ['X-Foo' => 'bar', 'Authorization' => 'Bearer token', 'User-Agent' => 'HypothesisClient']);
        $response = new FulfilledPromise(new ArrayResult(['foo' => ['bar', 'baz']]));
        $this->httpClient
            ->expects($this->once())
            ->method('send')
            ->with(RequestConstraint::equalTo($request))
            ->willReturn($response);
        $this->assertEquals($response, $this->searchClient->query([], 'username', 'token', 0, 20, true));
    }
}

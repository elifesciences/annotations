<?php

namespace tests\eLife\Annotations;

use eLife\ApiClient\ApiClient\ProfilesClient;
use eLife\ApiClient\MediaType;
use EmptyIterator;
use Traversable;

final class AnnotationsTest extends WebTestCase
{
    /**
     * @test
     * @dataProvider typeProvider
     */
    public function it_negotiates_type(string $type, int $statusCode)
    {
        $client = static::createClient();

        $this->mockHypothesisSearchCall('1234', $this->createAnnotations(), 20);

        $client->request('GET', '/annotations?by=1234', [], [], ['HTTP_ACCEPT' => $type]);
        $response = $client->getResponse();
        $this->assertSame($statusCode, $response->getStatusCode());
    }

    public function typeProvider() : Traversable
    {
        $types = [
            'application/vnd.elife.annotation-list+json' => 200,
            'application/vnd.elife.annotation-list+json; version=0' => 406,
            'application/vnd.elife.annotation-list+json; version=1' => 200,
            'application/vnd.elife.annotation-list+json; version=2' => 406,
            'text/plain' => 406,
        ];

        foreach ($types as $type => $statusCode) {
            yield $type => [$type, $statusCode];
        }
    }

    /**
     * @test
     */
    public function it_returns_404_if_user_unknown()
    {
        $client = static::createClient();
        $this->mockNotFound('profiles/1234', ['Accept' => new MediaType(ProfilesClient::TYPE_PROFILE, 1)]);
        $this->mockHypothesisSearchCall('1234', new EmptyIterator(), 0);
        $client->request('GET', '/annotations?by=1234');
        $response = $client->getResponse();
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('application/problem+json', $response->headers->get('Content-Type'));
        $this->assertResponseIsValid($response);
        $this->assertJsonStringEqualsJson(['title' => 'Unknown profile: 1234', 'type' => 'about:blank'], $response->getContent());
        $this->assertFalse($response->isCacheable());

        $this->mockProfileCall($this->createProfile('4321'));
        $this->mockHypothesisSearchCall('4321', new EmptyIterator(), 0);
        $client->request('GET', '/annotations?by=4321');
        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @test
     * @dataProvider invalidPageProvider
     */
    public function it_returns_a_404_for_an_invalid_page(string $page)
    {
        $client = static::createClient();

        $this->mockProfileCall($this->createProfile('1234'));
        $this->mockHypothesisSearchCall('1234', new EmptyIterator(), 0, (int) $page);

        $client->request('GET', "/annotations?by=1234&page=$page");
        $response = $client->getResponse();

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('application/problem+json', $response->headers->get('Content-Type'));
        $this->assertResponseIsValid($response);
        $this->assertJsonStringEqualsJson(['title' => "No page $page", 'type' => 'about:blank'], $response->getContent());
        $this->assertFalse($response->isCacheable());
    }

    public function invalidPageProvider() : Traversable
    {
        foreach (['-1', '0', '2', 'foo'] as $page) {
            yield 'page '.$page => [$page];
        }
    }
}

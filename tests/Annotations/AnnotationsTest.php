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
        $this->assertResponseIsValid($response);
        $this->assertSame($statusCode, $response->getStatusCode(), $response->getContent());
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
        $this->assertResponseIsValid($response);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('application/problem+json', $response->headers->get('Content-Type'));
        $this->assertResponseIsValid($response);
        $this->assertJsonStringEqualsJson(['title' => 'Unknown profile: 1234', 'type' => 'about:blank'], $response->getContent());
        $this->assertFalse($response->isCacheable());

        $this->mockProfileCall($this->createProfile('4321'));
        $this->mockHypothesisSearchCall('4321', new EmptyIterator(), 0);
        $client->request('GET', '/annotations?by=4321');
        $response = $client->getResponse();
        $this->assertResponseIsValid($response);
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
        $this->assertResponseIsValid($response);

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

    /**
     * @test
     */
    public function it_will_return_restricted_annotations()
    {
        $client = static::createClient();

        $this->mockHypothesisTokenCall('1234', '1234access');
        $this->mockHypothesisSearchCall('1234', $this->createAnnotations(), 20, 1, 20, '', 'desc', 'updated', ['Authorization' => 'Bearer 1234access']);
        $client->request('GET', '/annotations?by=1234&access=restricted', [], [], ['HTTP_X_CONSUMER_GROUPS' => 'user,view-restricted-annotations']);
        $response = $client->getResponse();
        $this->assertResponseIsValid($response);

        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_will_return_public_only_annotations_if_authorized_users_ask_for_them_exclusively()
    {
        $client = static::createClient();

        $this->mockHypothesisSearchCall('4321', $this->createAnnotations(), 20);
        $client->request('GET', '/annotations?by=4321&access=public', [], [], ['HTTP_X_CONSUMER_GROUPS' => 'user,view-restricted-annotations']);
        $response = $client->getResponse();
        $this->assertResponseIsValid($response);

        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_will_return_public_only_annotations_to_unauthorized_users()
    {
        $client = static::createClient();

        $this->mockHypothesisSearchCall('4321', $this->createAnnotations(), 20);
        $client->request('GET', '/annotations?by=4321');
        $response = $client->getResponse();
        $this->assertResponseIsValid($response);

        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_will_restrict_access_to_private_annotations()
    {
        $client = static::createClient();

        $this->mockHypothesisSearchCall('4321', $this->createAnnotations(), 20);
        $client->request('GET', '/annotations?by=4321&access=restricted');
        $response = $client->getResponse();
        $this->assertResponseIsValid($response);

        $this->assertSame(400, $response->getStatusCode());
    }
}

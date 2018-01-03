<?php

namespace tests\eLife\Annotations;

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

        $this->mockHypothesisSearchCall('1234', $this->createAnnotations(10), 20);

        $client->request('GET', '/annotations?by=1234', [], [], ['HTTP_ACCEPT' => $type]);
        $response = $client->getResponse();
        $this->assertMessageIsValid()
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
}

<?php

namespace tests\eLife\HypothesisClient\AppBundle\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use tests\eLife\HypothesisClient\WebTestCase;

class AnnotationsControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_must_have_a_by_filter()
    {
        $client = static::createClient(['debug' => false]);
        $client->request('GET', '/annotations');
        $response = $client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->headers->get('Content-Type'));
        $this->assertEquals('Missing by[] option', \GuzzleHttp\json_decode((string) $response->getContent())->title);
        $this->mockApiResponse(
            new Request(
                'GET',
                'https://hypothes.is/api/search?user=user&group=__world__&offset=0&limit=10&order=desc'
            ),
            new Response(
                200,
                [],
                json_encode([
                    'total' => 0,
                    'rows' => [],
                ])
            )
        );
        $client->request('GET', '/annotations?by[]=user');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     * @dataProvider byFilterProvider
     */
    public function it_must_have_a_valid_by_filter($by, $expectedStatus)
    {
        $by = (array) $by;
        $client = static::createClient(['debug' => false]);
        $this->mockApiResponse(
            new Request(
                'GET',
                'https://hypothes.is/api/search?user='.$by[0].'&group=__world__&offset=0&limit=10&order=desc'
            ),
            new Response(
                200,
                [],
                json_encode([
                    'total' => 0,
                    'rows' => [],
                ])
            )
        );
        $client->request('GET', '/annotations?by[]='.implode('&by[]=', $by));
        $response = $client->getResponse();
        $this->assertEquals($expectedStatus, $response->getStatusCode());

        if ($response->getStatusCode() === 400) {
            $this->assertEquals('application/problem+json', $response->headers->get('Content-Type'));
            $this->assertEquals('Invalid by[] option', \GuzzleHttp\json_decode((string) $response->getContent())->title);
        }
    }

    public function byFilterProvider() : array
    {
        return [
            'too short' => [
                'aa',
                400,
            ],
            'too long' => [
                'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz',
                400,
            ],
            'invalid character (?)' => [
                '?invalid',
                400,
            ],
            'invalid character ( )' => [
                ' invalid',
                400,
            ],
            'one invalid' => [
                ['valid', ' invalid'],
                400,
            ],
            'valid' => [
                'valid',
                200,
            ],
            'all valid' => [
                ['valid', 'another_valid'],
                200,
            ],
        ];
    }
}

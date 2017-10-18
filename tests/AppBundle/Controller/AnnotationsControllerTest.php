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
        if ($expectedStatus === 200) {
            $this->mockApiResponse(
                new Request(
                    'GET',
                    'https://hypothes.is/api/search?user='.$by[0].'&group=__world__&offset=0&limit=10&order=desc'
                ),
                new Response(
                    200,
                    [],
                    json_encode(
                        [
                            'total' => 0,
                            'rows' => [],
                        ]
                    )
                )
            );
        }
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

    /**
     * @test
     * @dataProvider pageFilterProvider
     */
    public function it_must_have_a_valid_page_filter($page, $expectedStatus)
    {
        $client = static::createClient(['debug' => false]);
        if ($expectedStatus === 200) {
            $this->mockApiResponse(
                new Request(
                    'GET',
                    'https://hypothes.is/api/search?user=user&group=__world__&offset='.(($page - 1) * 10).'&limit=10&order=desc'
                ),
                new Response(
                    200,
                    [],
                    json_encode(
                        [
                            'total' => 0,
                            'rows' => [],
                        ]
                    )
                )
            );
        }
        $client->request('GET', '/annotations?by[]=user&page='.$page);
        $response = $client->getResponse();
        $this->assertEquals($expectedStatus, $response->getStatusCode());

        if ($response->getStatusCode() === 400) {
            $this->assertEquals('application/problem+json', $response->headers->get('Content-Type'));
            $this->assertEquals('Invalid page option', \GuzzleHttp\json_decode((string) $response->getContent())->title);
        }
    }

    public function pageFilterProvider() : array
    {
        return [
            'non numeric' => [
                'a',
                400,
            ],
            'zero' => [
                0,
                400,
            ],
            'negative' => [
                -1,
                400,
            ],
            'valid' => [
                7,
                200,
            ],
            'valid lower limit' => [
                '1',
                200,
            ],
            'large valid' => [
                9999999,
                200,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider perPageFilterProvider
     */
    public function it_must_have_a_valid_per_page_filter($perPage, $expectedStatus)
    {
        $client = static::createClient(['debug' => false]);
        if ($expectedStatus === 200) {
            $this->mockApiResponse(
                new Request(
                    'GET',
                    'https://hypothes.is/api/search?user=user&group=__world__&offset=0&limit='.$perPage.'&order=desc'
                ),
                new Response(
                    200,
                    [],
                    json_encode(
                        [
                            'total' => 0,
                            'rows' => [],
                        ]
                    )
                )
            );
        }
        $client->request('GET', '/annotations?by[]=user&per-page='.$perPage);
        $response = $client->getResponse();
        $this->assertEquals($expectedStatus, $response->getStatusCode());

        if ($response->getStatusCode() === 400) {
            $this->assertEquals('application/problem+json', $response->headers->get('Content-Type'));
            $this->assertEquals('Invalid per-page option', \GuzzleHttp\json_decode((string) $response->getContent())->title);
        }
    }

    public function perPageFilterProvider() : array
    {
        return [
            'non numeric' => [
                'a',
                400,
            ],
            'too large' => [
                101,
                400,
            ],
            'zero' => [
                0,
                400,
            ],
            'negative' => [
                -1,
                400,
            ],
            'valid' => [
                7,
                200,
            ],
            'valid lower limit' => [
                '1',
                200,
            ],
            'valid upper limit' => [
                100,
                200,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider orderFilterProvider
     */
    public function it_must_have_a_valid_order_filter($order, $expectedStatus)
    {
        $client = static::createClient(['debug' => false]);
        if ($expectedStatus === 200) {
            $this->mockApiResponse(
                new Request(
                    'GET',
                    'https://hypothes.is/api/search?user=user&group=__world__&offset=0&limit=10&order='.strtolower(
                        $order
                    )
                ),
                new Response(
                    200,
                    [],
                    json_encode(
                        [
                            'total' => 0,
                            'rows' => [],
                        ]
                    )
                )
            );
        }
        $client->request('GET', '/annotations?by[]=user&order='.$order);
        $response = $client->getResponse();
        $this->assertEquals($expectedStatus, $response->getStatusCode());

        if ($response->getStatusCode() === 400) {
            $this->assertEquals('application/problem+json', $response->headers->get('Content-Type'));
            $this->assertEquals('Invalid order option', \GuzzleHttp\json_decode((string) $response->getContent())->title);
        }
    }

    public function orderFilterProvider() : array
    {
        return [
            'invalid' => [
                'invalid',
                400,
            ],
            'another invalid' => [
                1,
                400,
            ],
            'valid asc lowercase' => [
                'asc',
                200,
            ],
            'valid asc mixedcase' => [
                'Asc',
                200,
            ],
            'valid desc lowercase' => [
                'desc',
                200,
            ],
            'valid desc uppercase' => [
                'DESC',
                200,
            ],
        ];
    }
}

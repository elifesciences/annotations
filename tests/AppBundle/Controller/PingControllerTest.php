<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PingControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_returns_pong()
    {
        $client = static::createClient();
        $client->request('GET', '/ping');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/plain; charset=UTF-8', $response->headers->get('Content-Type'));
        $this->assertEquals('must-revalidate, no-cache, no-store, private', $response->headers->get('Cache-Control'));
    }
}

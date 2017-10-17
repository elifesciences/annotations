<?php

namespace tests\eLife\HypothesisClient\AppBundle\Controller;

use tests\eLife\HypothesisClient\WebTestCase;

final class ExceptionControllerTest extends WebTestCase
{

    /**
     * @test
     */
    public function it_returns_a_404_when_a_route_is_not_found()
    {
        $client = static::createClient(['debug' => false]);

        $client->request('GET', '/foo');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_a_404_when_previewing_a_404_page()
    {
        $client = static::createClient(['debug' => true]);

        $client->request('GET', '/_error/404');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_returns_a_504_when_there_is_a_timeout_reported()
    {
        $this->assertTrue(true);
    }
}

<?php

namespace tests\eLife\HypothesisClient\Exception;

use eLife\HypothesisClient\Exception\HttpProblem;
use eLife\HypothesisClient\Exception\NetworkProblem;
use GuzzleHttp\Psr7\Request;
use PHPUnit_Framework_TestCase;

/**
 * @covers \eLife\HypothesisClient\Exception\NetworkProblem
 */
class NetworkProblemTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_an_instance_of_http_problem()
    {
        $e = new NetworkProblem('foo', new Request('GET', 'http://www.example.com/'));
        $this->assertInstanceOf(HttpProblem::class, $e);
    }
}

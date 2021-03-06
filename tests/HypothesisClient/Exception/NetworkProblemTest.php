<?php

namespace tests\eLife\HypothesisClient\Exception;

use eLife\HypothesisClient\Exception\HttpProblem;
use eLife\HypothesisClient\Exception\NetworkProblem;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;

/**
 * @covers \eLife\HypothesisClient\Exception\NetworkProblem
 */
final class NetworkProblemTest extends TestCase
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

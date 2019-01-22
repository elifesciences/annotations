<?php

namespace tests\eLife\HypothesisClient\Exception;

use eLife\HypothesisClient\Exception\ApiTimeout;
use eLife\HypothesisClient\Exception\NetworkProblem;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;

/**
 * @covers \eLife\HypothesisClient\Exception\ApiTimeout
 */
final class ApiTimeoutTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_an_instance_of_network_problem()
    {
        $e = new ApiTimeout('foo', new Request('GET', 'http://www.example.com/'));
        $this->assertInstanceOf(NetworkProblem::class, $e);
    }
}

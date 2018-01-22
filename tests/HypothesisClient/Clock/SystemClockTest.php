<?php

namespace tests\eLife\HypothesisClient\Clock;

use eLife\HypothesisClient\Clock\Clock;
use eLife\HypothesisClient\Clock\SystemClock;
use PHPUnit_Framework_TestCase;

/**
 * @covers \eLife\HypothesisClient\Clock\SystemClock
 */
class SystemClockTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_return_the_current_time()
    {
        $clock = new SystemClock();
        $this->assertInstanceOf(Clock::class, $clock);
        $time = $clock->time();
        $this->assertGreaterThan(0, $time);
    }
}
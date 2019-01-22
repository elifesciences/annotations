<?php

namespace tests\eLife\HypothesisClient\Clock;

use eLife\HypothesisClient\Clock\Clock;
use eLife\HypothesisClient\Clock\SystemClock;
use PHPUnit\Framework\TestCase;

/**
 * @covers \eLife\HypothesisClient\Clock\SystemClock
 */
final class SystemClockTest extends TestCase
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

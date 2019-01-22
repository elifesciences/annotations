<?php

namespace tests\eLife\HypothesisClient\Clock;

use eLife\HypothesisClient\Clock\Clock;
use eLife\HypothesisClient\Clock\FixedClock;
use PHPUnit\Framework\TestCase;

/**
 * @covers \eLife\HypothesisClient\Clock\FixedClock
 */
final class FixedClockTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_return_a_fixed_time()
    {
        $clock = new FixedClock(1000);
        $this->assertInstanceOf(Clock::class, $clock);
        $this->assertSame(1000, $clock->time());
    }
}

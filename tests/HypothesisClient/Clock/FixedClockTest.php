<?php

namespace tests\eLife\HypothesisClient\Clock;

use eLife\HypothesisClient\Clock\Clock;
use eLife\HypothesisClient\Clock\FixedClock;
use PHPUnit_Framework_TestCase;

/**
 * @covers \eLife\HypothesisClient\Clock\FixedClock
 */
class FixedClockTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_return_a_fixed_time()
    {
        $clock = new FixedClock();
        $this->assertInstanceOf(Clock::class, $clock);
        $time = $clock->time();
        $this->assertGreaterThan(0, $time);
        sleep(1);
        $this->assertEquals($time, $clock->time());
    }
}

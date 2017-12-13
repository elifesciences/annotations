<?php

namespace tests\eLife\HypothesisClient\Clock;

use eLife\HypothesisClient\Clock\Clock;

/**
 * @covers \eLife\HypothesisClient\Clock\Clock
 */
class ClockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_return_the_current_time()
    {
        $clock = new Clock();
        $this->assertEquals(time(), $clock->time());
    }
}

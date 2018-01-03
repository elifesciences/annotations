<?php

namespace tests\eLife\HypothesisClient\Model\Annotation\Target\Selector;

use eLife\HypothesisClient\Model\Annotation\Target\Selector\Range;
use PHPUnit_Framework_TestCase;

/**
 * @covers \eLife\HypothesisClient\Model\Annotation\Target\Selector\Range
 */
final class RangeTest extends PHPUnit_Framework_TestCase
{
    /** @var Range */
    private $range;

    /**
     * @before
     */
    public function prepare_range()
    {
        $this->range = new Range('start_container', 'end_container', 0, 100);
    }

    /**
     * @test
     */
    public function it_has_a_start_container()
    {
        $this->assertEquals('start_container', $this->range->getStartContainer());
    }

    /**
     * @test
     */
    public function it_has_an_end_container()
    {
        $this->assertEquals('end_container', $this->range->getEndContainer());
    }

    /**
     * @test
     */
    public function it_has_a_start_offset()
    {
        $this->assertEquals(0, $this->range->getStartOffset());
    }

    /**
     * @test
     */
    public function it_has_an_end_offset()
    {
        $this->assertEquals(100, $this->range->getEndOffset());
    }
}

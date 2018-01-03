<?php

namespace tests\eLife\HypothesisClient\Model\Annotation\Target\Selector;

use eLife\HypothesisClient\Model\Annotation\Target\Selector\TextPosition;
use PHPUnit_Framework_TestCase;

/**
 * @covers \eLife\HypothesisClient\Model\Annotation\Target\Selector\TextPosition
 */
final class TextPositionTest extends PHPUnit_Framework_TestCase
{
    /** @var TextPosition */
    private $textPosition;

    /**
     * @before
     */
    public function prepare_text_position()
    {
        $this->textPosition = new TextPosition(1000, 2001);
    }

    /**
     * @test
     */
    public function it_has_a_start_position()
    {
        $this->assertEquals(1000, $this->textPosition->getStart());
    }

    /**
     * @test
     */
    public function it_has_an_end_position()
    {
        $this->assertEquals(2001, $this->textPosition->getEnd());
    }
}

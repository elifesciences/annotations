<?php

namespace tests\eLife\HypothesisClient\Model\Annotation\Target;

use eLife\HypothesisClient\Model\Annotation\Target\Selector;
use PHPUnit_Framework_TestCase;

/**
 * @covers \eLife\HypothesisClient\Model\Annotation\Target\Selector
 */
final class SelectorTest extends PHPUnit_Framework_TestCase
{
    /** @var Selector */
    private $selector;
    /** @var Selector\TextPosition */
    private $textPosition;
    /** @var Selector\TextQuote */
    private $textQuote;

    /**
     * @before
     */
    public function prepare_selector()
    {
        $this->selector = new Selector(
            $this->textPosition = new Selector\TextPosition(1000, 2001),
            $this->textQuote = new Selector\TextQuote('exact', 'prefix', 'suffix')
        );
    }

    /**
     * @test
     */
    public function it_has_a_text_position()
    {
        $this->assertEquals($this->textPosition, $this->selector->getTextPosition());
    }

    /**
     * @test
     */
    public function it_has_a_text_quote()
    {
        $this->assertEquals($this->textQuote, $this->selector->getTextQuote());
    }

    /**
     * @test
     */
    public function it_may_have_a_range()
    {
        $without = $this->selector;
        $with = new Selector(
            $this->textPosition,
            $this->textQuote,
            $range = new Selector\Range('start_container', 'end_container', 0, 100)
        );
        $this->assertNull($without->getRange());
        $this->assertEquals($range, $with->getRange());
    }

    /**
     * @test
     */
    public function it_may_have_a_fragment()
    {
        $without = $this->selector;
        $with = new Selector(
            $this->textPosition,
            $this->textQuote,
            null,
            $fragment = new Selector\Fragment('conforms_to', 'value')
        );
        $this->assertNull($without->getFragment());
        $this->assertEquals($fragment, $with->getFragment());
    }
}

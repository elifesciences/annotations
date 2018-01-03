<?php

namespace tests\eLife\HypothesisClient\Model\Annotation\Target;

use eLife\HypothesisClient\Model\Annotation\Target\Selector;
use PHPUnit_Framework_TestCase;

/**
 * @covers \eLife\HypothesisClient\Model\Annotation\Target\Selector
 */
final class SelectorTest extends PHPUnit_Framework_TestCase
{
    /** @var Selector\Range */
    private $range;
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
            $this->range = new Selector\Range('start_container', 'end_container', 0, 100),
            $this->textPosition = new Selector\TextPosition(1000, 2001),
            $this->textQuote = new Selector\TextQuote('exact', 'prefix', 'suffix')
        );
    }

    /**
     * @test
     */
    public function it_has_a_range()
    {
        $this->assertEquals($this->range, $this->selector->getRange());
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
    public function it_may_have_a_fragment()
    {
        $selectorWithoutFragment = $this->selector;
        $selectorWithFragment = new Selector(
            $this->range,
            $this->textPosition,
            $this->textQuote,
            $fragment = new Selector\Fragment('conforms_to', 'value')
        );
        $this->assertNull($selectorWithoutFragment->getFragment());
        $this->assertEquals($fragment, $selectorWithFragment->getFragment());
    }
}

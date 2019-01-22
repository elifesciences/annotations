<?php

namespace tests\eLife\HypothesisClient\Model\Annotation\Target;

use eLife\HypothesisClient\Model\Annotation\Target\Selector;
use PHPUnit\Framework\TestCase;

/**
 * @covers \eLife\HypothesisClient\Model\Annotation\Target\Selector
 */
final class SelectorTest extends TestCase
{
    /** @var Selector */
    private $selector;
    /** @var Selector\TextQuote */
    private $textQuote;

    /**
     * @before
     */
    public function prepare_selector()
    {
        $this->selector = new Selector(
            $this->textQuote = new Selector\TextQuote('exact', 'prefix', 'suffix')
        );
    }

    /**
     * @test
     */
    public function it_has_a_text_quote()
    {
        $this->assertSame($this->textQuote, $this->selector->getTextQuote());
    }
}

<?php

namespace tests\eLife\HypothesisClient\Model\Annotation\Target\Selector;

use eLife\HypothesisClient\Model\Annotation\Target\Selector\TextQuote;
use PHPUnit_Framework_TestCase;

/**
 * @covers \eLife\HypothesisClient\Model\Annotation\Target\Selector\TextQuote
 */
final class TextQuoteTest extends PHPUnit_Framework_TestCase
{
    /** @var TextQuote */
    private $textQuote;

    /**
     * @before
     */
    public function prepare_text_quote()
    {
        $this->textQuote = new TextQuote('exact', 'prefix', 'suffix');
    }

    /**
     * @test
     */
    public function it_has_exact_text()
    {
        $this->assertSame('exact', $this->textQuote->getExact());
    }

    /**
     * @test
     */
    public function it_has_a_prefix()
    {
        $this->assertSame('prefix', $this->textQuote->getPrefix());
    }

    /**
     * @test
     */
    public function it_has_a_suffix()
    {
        $this->assertSame('suffix', $this->textQuote->getSuffix());
    }
}

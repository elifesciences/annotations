<?php

namespace tests\eLife\HypothesisClient\Model\Annotation;

use eLife\HypothesisClient\Model\Annotation\Document;
use PHPUnit_Framework_TestCase;

/**
 * @covers \eLife\HypothesisClient\Model\Annotation\Document
 */
final class DocumentTest extends PHPUnit_Framework_TestCase
{
    /** @var Document */
    private $document;

    /**
     * @before
     */
    public function prepare_document()
    {
        $this->document = new Document('title');
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $this->assertSame('title', $this->document->getTitle());
    }
}

<?php

namespace test\eLife\ApiSdk\Model;

use eLife\HypothesisClient\ApiSdk\Model\Annotation;
use eLife\HypothesisClient\ApiSdk\Model\Links;
use eLife\HypothesisClient\ApiSdk\Model\Model;
use PHPUnit_Framework_TestCase;

final class AnnotationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_model()
    {
        $annotation = new Annotation('id', new Links('http://url.incontext'));

        $this->assertInstanceOf(Model::class, $annotation);
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $annotation = new Annotation('id', new Links('http://url.incontext'));

        $this->assertSame('id', $annotation->getId());
    }

    /**
     * @test
     */
    public function it_has_links()
    {
        $annotation = new Annotation('id', $links = new Links('http://url.incontext'));

        $this->assertEquals($links, $annotation->getLinks());
    }

    /**
     * @test
     */
    public function it_may_have_text()
    {
        $with = new Annotation('id', $links = new Links('http://url.incontext'), 'text');
        $withOut = new Annotation('id', $links = new Links('http://url.incontext'), null);

        $this->assertSame('text', $with->getText());
        $this->assertNull($withOut->getText());
    }
}

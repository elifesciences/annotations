<?php

namespace test\eLife\ApiSdk\Model;

use eLife\HypothesisClient\ApiSdk\Model\Annotation;
use PHPUnit_Framework_TestCase;

final class AnnotationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_an_id()
    {
        $annotation = new Annotation('id');

        $this->assertSame('id', $annotation->getId());
    }
}

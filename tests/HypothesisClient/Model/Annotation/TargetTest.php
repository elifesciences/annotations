<?php

namespace tests\eLife\HypothesisClient\Model\Annotation;

use eLife\HypothesisClient\Model\Annotation\Target;
use PHPUnit\Framework\TestCase;

/**
 * @covers \eLife\HypothesisClient\Model\Annotation\Target
 */
final class TargetTest extends TestCase
{
    /** @var Target */
    private $target;

    /**
     * @before
     */
    public function prepare_target()
    {
        $this->target = new Target('source');
    }

    /**
     * @test
     */
    public function it_has_a_source()
    {
        $this->assertSame('source', $this->target->getSource());
    }

    /**
     * @test
     */
    public function it_may_have_a_selector()
    {
        $targetWithoutSelector = $this->target;
        $targetWithSelector = new Target(
            'source',
            $selector = new Target\Selector(
                new Target\Selector\TextQuote('exact', 'prefix', 'suffix')
            )
        );
        $this->assertNull($targetWithoutSelector->getSelector());
        $this->assertSame($selector, $targetWithSelector->getSelector());
    }
}

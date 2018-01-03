<?php

namespace tests\eLife\HypothesisClient\Model\Annotation\Target\Selector;

use eLife\HypothesisClient\Model\Annotation\Target\Selector\Fragment;
use PHPUnit_Framework_TestCase;

/**
 * @covers \eLife\HypothesisClient\Model\Annotation\Target\Selector\Fragment
 */
final class FragmentTest extends PHPUnit_Framework_TestCase
{
    /** @var Fragment */
    private $fragment;

    /**
     * @before
     */
    public function prepare_fragment()
    {
        $this->fragment = new Fragment('conforms_to', 'value');
    }

    /**
     * @test
     */
    public function it_has_a_conforms_to_reference()
    {
        $this->assertEquals('conforms_to', $this->fragment->getConformsTo());
    }

    /**
     * @test
     */
    public function it_has_a_value()
    {
        $this->assertEquals('value', $this->fragment->getValue());
    }
}

<?php

namespace test\eLife\HypothesisClient\ApiSdk\Collection;

use BadMethodCallException;
use eLife\HypothesisClient\ApiSdk\Collection\EmptySequence;
use eLife\HypothesisClient\ApiSdk\Collection\Sequence;
use Exception;
use PHPUnit_Framework_TestCase;

final class EmptySequenceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $collection = new EmptySequence();

        $this->assertInstanceOf(Sequence::class, $collection);
    }

    /**
     * @test
     */
    public function it_cannot_be_traversed()
    {
        $collection = new EmptySequence();

        foreach ($collection as $i => $element) {
            throw new Exception('Should not be traversed');
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $collection = new EmptySequence();

        $this->assertTrue($collection->isEmpty());
        $this->assertFalse($collection->notEmpty());
        $this->assertSame(0, $collection->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $collection = new EmptySequence();

        $this->assertSame([], $collection->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $collection = new EmptySequence();

        $this->assertFalse(isset($collection[1]));
        $this->assertSame(null, $collection[1]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $collection = new EmptySequence();

        $this->expectException(BadMethodCallException::class);

        $collection[0] = 'foo';
    }

    /**
     * @test
     */
    public function it_can_be_prepended()
    {
        $collection = new EmptySequence();

        $collection = $collection->prepend(0, 1);

        $this->assertNotInstanceOf(EmptySequence::class, $collection);
        $this->assertSame([0, 1], $collection->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_appended()
    {
        $collection = new EmptySequence();

        $collection = $collection->append(0, 1);

        $this->assertNotInstanceOf(EmptySequence::class, $collection);
        $this->assertSame([0, 1], $collection->toArray());
    }

    /**
     * @test
     */
    public function it_can_not_have_values_dropped()
    {
        $collection = new EmptySequence();

        $this->assertEquals($collection, $collection->drop(1, 3));
    }

    /**
     * @test
     */
    public function it_can_have_values_inserted()
    {
        $collection = new EmptySequence();

        $collection = $collection->insert(2, 'foo', 'bar');

        $this->assertNotInstanceOf(EmptySequence::class, $collection);
        $this->assertSame(['foo', 'bar'], $collection->toArray());
    }

    /**
     * @test
     */
    public function it_can_have_values_set()
    {
        $collection = new EmptySequence();

        $collection = $collection->set(2, 'foo');

        $this->assertNotInstanceOf(EmptySequence::class, $collection);
        $this->assertSame(['foo'], $collection->toArray());
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null)
    {
        $collection = new EmptySequence();

        $this->assertEquals($collection, $collection->slice($offset, $length));
    }

    public function sliceProvider() : array
    {
        return [
            'offset 1, length 1' => [1, 1],
            'offset -2, no length' => [-2, null],
            'offset 6, no length' => [6, null],
        ];
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_cannot_be_mapped()
    {
        $collection = new EmptySequence();

        $map = function () {
            throw new Exception('Should not be mapped');
        };

        $this->assertEquals($collection, $collection->map($map));
    }

    /**
     * @test
     */
    public function it_cannot_be_filtered()
    {
        $collection = new EmptySequence();

        $this->assertEquals($collection, $collection->filter());
    }

    /**
     * @test
     */
    public function it_can_be_filtered_with_a_callback()
    {
        $collection = new EmptySequence();

        $filter = function () {
            throw new Exception('Should not be filtered');
        };

        $this->assertEquals($collection, $collection->filter($filter));
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $collection = new EmptySequence();

        $reduce = function () {
            throw new Exception('Should not be reduced');
        };

        $this->assertSame(100, $collection->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $collection = new EmptySequence();

        $this->assertEquals($collection, $collection->sort());
    }

    /**
     * @test
     */
    public function it_can_be_flattened()
    {
        $collection = new EmptySequence();

        $this->assertEquals($collection, $collection->flatten());
    }

    /**
     * @test
     */
    public function it_can_be_sorted_with_a_callback()
    {
        $collection = new EmptySequence();

        $sort = function () {
            throw new Exception('Should not be sorted');
        };

        $this->assertEquals($collection, $collection->sort($sort));
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $collection = new EmptySequence();

        $this->assertEquals($collection, $collection->reverse());
    }
}

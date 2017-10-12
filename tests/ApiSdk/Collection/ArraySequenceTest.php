<?php

namespace tests\eLife\HypothesisClient\ApiSdk\Collection;

use BadMethodCallException;
use eLife\HypothesisClient\ApiSdk\Collection\ArraySequence;
use eLife\HypothesisClient\ApiSdk\Collection\Sequence;
use PHPUnit_Framework_TestCase;

final class ArraySequenceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $collection = new ArraySequence([1, 2, 3, 4, 5]);

        $this->assertInstanceOf(Sequence::class, $collection);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $collection = new ArraySequence([1, 2, 3, 4, 5]);

        foreach ($collection as $i => $element) {
            $this->assertSame($i + 1, $element);
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $collection = new ArraySequence([1, 2, 3, 4, 5]);

        $this->assertFalse($collection->isEmpty());
        $this->assertSame(5, $collection->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $array = [1, 2, 3, 4, 5];

        $collection = new ArraySequence($array);

        $this->assertSame($array, $collection->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $collection = new ArraySequence([1, 2, 3, 4, 5]);

        $this->assertTrue(isset($collection[0]));
        $this->assertSame(1, $collection[0]);
        $this->assertFalse(isset($collection[5]));
        $this->assertSame(null, $collection[5]);
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $collection = new ArraySequence([1, 2, 3, 4, 5]);

        $this->expectException(BadMethodCallException::class);

        $collection[0] = 'foo';
    }

    /**
     * @test
     */
    public function it_can_be_prepended()
    {
        $collection = new ArraySequence([1, 2, 3, 4, 5]);

        $collection = $collection->prepend(-1, 0);

        $this->assertSame([-1, 0, 1, 2, 3, 4, 5], $collection->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_appended()
    {
        $collection = new ArraySequence([1, 2, 3, 4, 5]);

        $collection = $collection->append(6, 7);

        $this->assertSame([1, 2, 3, 4, 5, 6, 7], $collection->toArray());
    }

    /**
     * @test
     * @dataProvider dropProvider
     */
    public function it_can_have_values_dropped(array $drop, array $expected)
    {
        $collection = new ArraySequence([1, 2, 3, 4, 5]);

        $collection = $collection->drop(...$drop);

        $this->assertSame($expected, $collection->toArray());
    }

    public function dropProvider() : array
    {
        return [
            '1, 3' => [[1, 3], [1, 3, 5]],
            '100' => [[100], [1, 2, 3, 4, 5]],
            '-2' => [[-2], [1, 2, 3, 5]],
            '-2, 1' => [[-2, 1], [1, 3, 5]],
            '4, -2' => [[4, -2], [1, 2, 3]],
        ];
    }

    /**
     * @test
     * @dataProvider insertProvider
     */
    public function it_can_have_values_inserted(int $index, array $expected)
    {
        $collection = new ArraySequence([1, 2, 3, 4, 5]);

        $collection = $collection->insert($index, 'foo');

        $this->assertSame($expected, $collection->toArray());
    }

    public function insertProvider() : array
    {
        return [
            '2' => [2, [1, 2, 'foo', 3, 4, 5]],
            '100' => [100, [1, 2, 3, 4, 5, 'foo']],
            '-2' => [-2, [1, 2, 3, 'foo', 4, 5]],
            '-100' => [-100, ['foo', 1, 2, 3, 4, 5]],
        ];
    }

    /**
     * @test
     * @dataProvider setProvider
     */
    public function it_can_have_a_value_set(int $index, array $expected)
    {
        $collection = new ArraySequence([1, 2, 3, 4, 5]);

        $collection = $collection->set($index, 'foo');

        $this->assertSame($expected, $collection->toArray());
    }

    public function setProvider() : array
    {
        return [
            'index 0' => [0, ['foo', 2, 3, 4, 5]],
            'index 2' => [2, [1, 2, 'foo', 4, 5]],
            'index 100' => [100, [1, 2, 3, 4, 5, 'foo']],
            'index -1' => [-1, [1, 2, 3, 4, 'foo']],
            'index -2' => [-2, [1, 2, 3, 'foo', 5]],
        ];
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected)
    {
        $collection = new ArraySequence([1, 2, 3, 4, 5]);

        $this->assertSame($expected, $collection->slice($offset, $length)->toArray());
    }

    public function sliceProvider() : array
    {
        return [
            'offset 1, length 1' => [1, 1, [2]],
            'offset -2, no length' => [-2, null, [4, 5]],
            'offset 6, no length' => [6, null, []],
        ];
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_mapped()
    {
        $collection = new ArraySequence([1, 2, 3, 4, 5]);

        $map = function (int $number) {
            return $number * 100;
        };

        $this->assertSame([100, 200, 300, 400, 500], $collection->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_also_provides_the_index_when_mapping()
    {
        $collection = new ArraySequence([5, 9, 100]);

        $map = function (int $value, int $index) {
            return $index;
        };

        $this->assertSame([0, 1, 2], $collection->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $collection = new ArraySequence([1, null, 2, 3, false, 4, 5]);

        $this->assertSame([1, 2, 3, 4, 5], $collection->filter()->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered_with_a_callback()
    {
        $collection = new ArraySequence([1, 2, 3, 4, 5]);

        $filter = function (int $number) {
            return $number > 3;
        };

        $this->assertSame([4, 5], $collection->filter($filter)->toArray());
    }

    /**
     * @test
     */
    public function it_also_provides_the_index_when_filtering_with_a_callback()
    {
        $collection = new ArraySequence([5, 9, 100]);

        $filter = function (int $value, int $index) {
            return $index > 0;
        };

        $this->assertSame([9, 100], $collection->filter($filter)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $collection = new ArraySequence([1, 2, 3, 4, 5]);

        $reduce = function (int $carry = null, int $number) {
            return $carry + $number;
        };

        $this->assertSame(115, $collection->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_can_be_flattened()
    {
        $collection = new ArraySequence([1, new ArraySequence([2, new ArraySequence([3, 4]), 5]), 6]);

        $this->assertSame([1, 2, 3, 4, 5, 6], $collection->flatten()->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $collection = new ArraySequence([5, 4, 3, 2, 1]);

        $this->assertSame([1, 2, 3, 4, 5], $collection->sort()->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_sorted_with_a_callback()
    {
        $collection = new ArraySequence([1, 2, 3, 4, 5]);

        $sort = function (int $a, int $b) {
            return $b <=> $a;
        };

        $this->assertSame([5, 4, 3, 2, 1], $collection->sort($sort)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $collection = new ArraySequence([1, 2, 3, 4, 5]);

        $this->assertSame([5, 4, 3, 2, 1], $collection->reverse()->toArray());
    }
}

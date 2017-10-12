<?php

namespace tests\eLife\HypothesisClient\ApiSdk\Client;

use BadMethodCallException;
use eLife\HypothesisClient\ApiSdk\ApiSdk;
use eLife\HypothesisClient\ApiSdk\Client\Annotations;
use eLife\HypothesisClient\ApiSdk\Collection\Sequence;
use eLife\HypothesisClient\ApiSdk\Model\Annotation;
use tests\eLife\HypothesisClient\ApiSdk\ApiTestCase;

final class AnnotationsTest extends ApiTestCase
{
    use SlicingTestCase;

    /** @var Annotations */
    private $annotations;

    /**
     * @before
     */
    protected function setUpAnnotations()
    {
        $this->annotations = (new ApiSdk($this->getHttpClient()))->annotations();
    }

    /**
     * @test
     */
    public function it_is_a_sequence()
    {
        $list = $this->annotations->get('foo', 'group');

        $this->assertInstanceOf(Sequence::class, $list);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $list = $this->annotations->get('foo', 'group');

        $this->mockAnnotationsCall('foo', 'group', 1, 1, 200);
        $this->mockAnnotationsCall('foo', 'group', 1, 100, 200);
        $this->mockAnnotationsCall('foo', 'group', 2, 100, 200);

        $this->assertSame(200, $this->traverseAndSanityCheck($this->annotations->get('foo', 'group')));
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $list = $this->annotations->get('foo', 'group');

        $this->mockAnnotationsCall('foo', 'group', 1, 1, 10);

        $this->assertFalse($list->isEmpty());
        $this->assertSame(10, $list->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $list = $this->annotations->get('foo', 'group');

        $this->mockAnnotationsCall('foo', 'group', 1, 1, 10);
        $this->mockAnnotationsCall('foo', 'group', 1, 100, 10);

        $this->assertSame(10, $this->traverseAndSanityCheck($list->toArray()));
    }

    /**
     * @test
     */
    public function it_can_be_accessed_like_an_array()
    {
        $list = $this->annotations->get('foo', 'group');

        $this->mockAnnotationsCall('foo', 'group', 1, 1, 1);

        $this->assertTrue(isset($list[0]));
        $this->assertSame('annotation-1', $list[0]->getId());
    }

    /**
     * @test
     */
    public function it_is_an_immutable_array()
    {
        $list = $this->annotations->get('foo', 'group');

        $this->expectException(BadMethodCallException::class);

        $list[0] = 'foo';
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_sliced(int $offset, int $length = null, array $expected, array $calls)
    {
        $list = $this->annotations->get('foo', 'group');

        foreach ($calls as $call) {
            $this->mockAnnotationsCall('foo', 'group', $call['page'], $call['per-page'], 5);
        }

        foreach ($list->slice($offset, $length) as $i => $highlight) {
            $this->assertInstanceOf(Annotation::class, $highlight);
            $this->assertSame('annotation-'.$expected[$i], $highlight->getId());
        }
    }

    /**
     * @test
     * @dataProvider sliceProvider
     */
    public function it_can_be_mapped()
    {
        $list = $this->annotations->get('foo', 'group');

        $this->mockAnnotationsCall('foo', 'group', 1, 1, 3);
        $this->mockAnnotationsCall('foo', 'group', 1, 100, 3);

        $map = function (Annotation $annotation) {
            return $annotation->getId();
        };

        $this->assertSame(['annotation-1', 'annotation-2', 'annotation-3'], $list->map($map)->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $list = $this->annotations->get('foo', 'group');

        $this->mockAnnotationsCall('foo', 'group', 1, 1, 5);
        $this->mockAnnotationsCall('foo', 'group', 1, 100, 5);

        $filter = function (Annotation $annotation, int $key) {
            return $key >= 3;
        };

        foreach ($list->filter($filter) as $i => $annotation) {
            $expected = $i + 4;
            $this->assertSame("annotation-$expected", $annotation->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reduced()
    {
        $list = $this->annotations->get('foo', 'group');

        $this->mockAnnotationsCall('foo', 'group', 1, 1, 5);
        $this->mockAnnotationsCall('foo', 'group', 1, 100, 5);

        $reduce = function (int $carry = null, Annotation $annotation) {
            return $carry + substr($annotation->getId(), -1);
        };

        $this->assertSame(115, $list->reduce($reduce, 100));
    }

    /**
     * @test
     */
    public function it_can_be_sorted()
    {
        $list = $this->annotations->get('foo', 'group');

        $this->mockAnnotationsCall('foo', 'group', 1, 1, 5);
        $this->mockAnnotationsCall('foo', 'group', 1, 100, 5);

        $sort = function (Annotation $a, Annotation $b) {
            return $b->getId() <=> $a->getId();
        };

        foreach ($list->sort($sort) as $i => $highlight) {
            $expected = 5 - $i;
            $this->assertSame("annotation-$expected", $highlight->getId());
        }
    }

    /**
     * @test
     */
    public function it_can_be_reversed()
    {
        $list = $this->annotations->get('foo', 'group');

        $this->mockAnnotationsCall('foo', 'group', 1, 1, 5, false);
        $this->mockAnnotationsCall('foo', 'group', 1, 100, 5, false);

        foreach ($list->reverse() as $i => $annotations) {
            $this->assertSame("annotation-$i", $annotations->getId());
        }
    }

    /**
     * @test
     */
    public function it_does_not_recount_when_reversed()
    {
        $list = $this->annotations->get('foo', 'group');

        $this->mockAnnotationsCall('foo', 'group', 1, 1, 10);

        $list->count();

        $this->assertSame(10, $list->reverse()->count());
    }

    /**
     * @test
     */
    public function it_fetches_pages_again_when_reversed()
    {
        $list = $this->annotations->get('foo', 'group');

        $this->mockAnnotationsCall('foo', 'group', 1, 1, 200);
        $this->mockAnnotationsCall('foo', 'group', 1, 100, 200);
        $this->mockAnnotationsCall('foo', 'group', 2, 100, 200);

        $list->toArray();

        $this->mockAnnotationsCall('foo', 'group', 1, 1, 200, false);
        $this->mockAnnotationsCall('foo', 'group', 1, 100, 200, false);
        $this->mockAnnotationsCall('foo', 'group', 2, 100, 200, false);

        $list->reverse()->toArray();
    }

    private function traverseAndSanityCheck($search)
    {
        $count = 0;
        foreach ($search as $i => $model) {
            $this->assertInstanceOf(Annotation::class, $model);
            ++$count;
        }

        return $count;
    }
}

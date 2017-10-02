<?php

namespace spec\eLife\HypothesisClient\Result;

use ArrayAccess;
use ArrayIterator;
use BadMethodCallException;
use Countable;
use IteratorAggregate;
use PhpSpec\ObjectBehavior;

final class ArrayResultSpec extends ObjectBehavior
{
    private $data;

    public function let()
    {
        $this->data = ['foo' => ['bar', 'baz']];

        $this->beConstructedWith($this->data);
    }

    public function it_casts_to_any_array()
    {
        $this->toArray()->shouldBeLike($this->data);
    }

    public function it_can_be_searched()
    {
        $this->search('foo[1]')->shouldBeLike(array_pop($this->data['foo']));
    }

    public function it_can_be_counted()
    {
        $this->shouldHaveType(Countable::class);
        $this->count()->shouldBe(count($this->data));
    }

    public function it_can_be_iterated()
    {
        $this->shouldHaveType(IteratorAggregate::class);
        $this->getIterator()->shouldBeLike(new ArrayIterator($this->data));
    }

    public function it_can_be_accessed_like_an_array()
    {
        $this->shouldHaveType(ArrayAccess::class);
        $this->offsetExists('foo')->shouldBe(true);
        $this->offsetGet('foo')->shouldBeLike($this->data['foo']);
    }

    public function it_is_immutable()
    {
        $this->shouldThrow(BadMethodCallException::class)->duringOffsetSet('foo', 'bar');
        $this->shouldThrow(BadMethodCallException::class)->duringOffsetUnset('foo');
    }
}

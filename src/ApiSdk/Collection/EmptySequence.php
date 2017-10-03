<?php

namespace eLife\HypothesisClient\ApiSdk\Collection;

use eLife\HypothesisClient\ApiSdk\CanBeCounted;
use eLife\HypothesisClient\ApiSdk\Collection;
use eLife\HypothesisClient\ApiSdk\ImmutableArrayAccess;
use EmptyIterator;
use IteratorAggregate;
use Traversable;

final class EmptySequence implements IteratorAggregate, Sequence
{
    use CanBeCounted;
    use ImmutableArrayAccess;

    public function getIterator() : Traversable
    {
        return new EmptyIterator();
    }

    public function count() : int
    {
        return 0;
    }

    public function toArray() : array
    {
        return [];
    }

    public function prepend(...$values) : Sequence
    {
        return new ArraySequence($values);
    }

    public function append(...$values) : Sequence
    {
        return new ArraySequence($values);
    }

    public function drop(int ...$indexes) : Sequence
    {
        return $this;
    }

    public function insert(int $index, ...$values) : Sequence
    {
        return new ArraySequence($values);
    }

    public function set(int $index, $value) : Sequence
    {
        return new ArraySequence([$value]);
    }

    public function slice(int $offset, int $length = null) : Sequence
    {
        return $this;
    }

    public function map(callable $callback) : Sequence
    {
        return $this;
    }

    public function filter(callable $callback = null) : Collection
    {
        return $this;
    }

    public function reduce(callable $callback, $initial = null)
    {
        return $initial;
    }

    public function flatten() : Sequence
    {
        return $this;
    }

    public function sort(callable $callback = null) : Sequence
    {
        return $this;
    }

    public function reverse() : Sequence
    {
        return $this;
    }

    public function offsetExists($offset) : bool
    {
        return false;
    }

    public function offsetGet($offset)
    {
        return null;
    }
}

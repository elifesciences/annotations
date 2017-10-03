<?php

namespace eLife\HypothesisClient\ApiSdk\Collection;

use ArrayIterator;
use eLife\HypothesisClient\ApiSdk\CanBeCounted;
use eLife\HypothesisClient\ApiSdk\Collection;
use eLife\HypothesisClient\ApiSdk\ImmutableArrayAccess;
use IteratorAggregate;
use Traversable;

final class ArraySequence implements IteratorAggregate, Sequence
{
    use CanBeCounted;
    use ImmutableArrayAccess;

    private $array;

    public function __construct(array $array)
    {
        $this->array = array_values($array);
    }

    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->array);
    }

    public function count() : int
    {
        return count($this->array);
    }

    public function toArray() : array
    {
        return $this->array;
    }

    public function prepend(...$values) : Sequence
    {
        return new self(array_merge($values, $this->array));
    }

    public function append(...$values) : Sequence
    {
        return new self(array_merge($this->array, $values));
    }

    public function drop(int ...$indexes) : Sequence
    {
        $indexes = array_unique(array_map(function (int $index) {
            if ($index < 0) {
                $index = count($this->array) + $index;
            }

            return $index;
        }, $indexes));

        return $this->filter(function ($value, int $index) use ($indexes) {
            return !in_array($index, $indexes, true);
        });
    }

    public function insert(int $index, ...$values) : Sequence
    {
        $clone = clone $this;

        array_splice($clone->array, $index, 0, $values);

        return $clone;
    }

    public function set(int $index, $value) : Sequence
    {
        $collection = $this->drop($index);

        if (-1 === $index) {
            return $collection->append($value);
        } elseif ($index < 0) {
            ++$index;
        }

        return $collection->insert($index, $value);
    }

    public function slice(int $offset, int $length = null) : Sequence
    {
        return new self(array_slice($this->array, $offset, $length));
    }

    public function map(callable $callback) : Sequence
    {
        return new self(array_map($callback, $this->array, array_keys($this->array)));
    }

    public function filter(callable $callback = null) : Collection
    {
        if (null === $callback) {
            return new self(array_filter($this->array));
        }

        return new self(array_filter($this->array, $callback, ARRAY_FILTER_USE_BOTH));
    }

    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->array, $callback, $initial);
    }

    public function flatten() : Sequence
    {
        return $this->reduce(function (Sequence $sequence, $item) {
            if ($item instanceof Sequence) {
                return $sequence->append(...$item->flatten());
            }

            return $sequence->append($item);
        }, new EmptySequence());
    }

    public function sort(callable $callback = null) : Sequence
    {
        $clone = clone $this;

        if (null === $callback) {
            sort($clone->array);
        } else {
            usort($clone->array, $callback);
        }

        return $clone;
    }

    public function reverse() : Sequence
    {
        return new self(array_reverse($this->array));
    }

    public function offsetExists($offset) : bool
    {
        return isset($this->array[$offset]);
    }

    public function offsetGet($offset)
    {
        if (!isset($this->array[$offset])) {
            return null;
        }

        return $this->array[$offset];
    }
}

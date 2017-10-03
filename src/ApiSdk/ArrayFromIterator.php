<?php

namespace eLife\HypothesisClient\ApiSdk;

use eLife\HypothesisClient\ApiSdk\Collection\ArraySequence;
use eLife\HypothesisClient\ApiSdk\Collection\PromiseSequence;
use eLife\HypothesisClient\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\Promise;

trait ArrayFromIterator
{
    final public function prepend(...$values) : Sequence
    {
        return $this->all()->prepend(...$values);
    }

    final public function append(...$values) : Sequence
    {
        return $this->all()->append(...$values);
    }

    final public function drop(int ...$indexes) : Sequence
    {
        return $this->all()->drop(...$indexes);
    }

    final public function insert(int $index, ...$values) : Sequence
    {
        return $this->all()->insert($index, ...$values);
    }

    final public function set(int $index, $value) : Sequence
    {
        return $this->all()->set($index, $value);
    }

    final public function map(callable $callback) : Sequence
    {
        return $this->all()->map($callback);
    }

    final public function filter(callable $callback = null) : Collection
    {
        return $this->all()->filter($callback);
    }

    final public function reduce(callable $callback, $initial = null)
    {
        return $this->all()->reduce($callback, $initial);
    }

    final public function sort(callable $callback = null) : Sequence
    {
        return $this->all()->sort($callback);
    }

    final public function toArray() : array
    {
        $array = [];

        foreach ($this as $item) {
            $array[] = $item;
        }

        return $array;
    }

    final private function all() : PromiseSequence
    {
        return new PromiseSequence(
            $promise = new Promise(
                function () use (&$promise) {
                    $promise->resolve(new ArraySequence($this->toArray()));
                }
            )
        );
    }
}

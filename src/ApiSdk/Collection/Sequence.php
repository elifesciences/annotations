<?php

namespace eLife\HypothesisClient\ApiSdk\Collection;

use ArrayAccess;
use eLife\HypothesisClient\ApiSdk\Collection;

interface Sequence extends Collection, ArrayAccess
{
    public function prepend(...$values) : Sequence;

    public function append(...$values) : Sequence;

    public function drop(int ...$offsets) : Sequence;

    public function insert(int $index, ...$values) : Sequence;

    public function set(int $index, $value) : Sequence;

    public function map(callable $callback) : Sequence;

    public function slice(int $offset, int $length = null) : Sequence;

    /**
     * @return Sequence
     */
    public function filter(callable $callback = null) : Collection;

    public function reduce(callable $callback, $initial = null);

    public function flatten() : Sequence;

    public function sort(callable $callback = null) : Sequence;

    public function reverse() : Sequence;
}

<?php

namespace eLife\HypothesisClient\ApiSdk;

use Countable;
use Traversable;

interface Collection extends Countable, Traversable
{
    public function filter(callable $callback = null) : Collection;

    public function isEmpty() : bool;

    public function notEmpty() : bool;

    public function toArray() : array;
}

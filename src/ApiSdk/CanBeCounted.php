<?php

namespace eLife\HypothesisClient\ApiSdk;

trait CanBeCounted
{
    abstract public function count() : int;

    final public function isEmpty() : bool
    {
        return 0 === count($this);
    }

    final public function notEmpty() : bool
    {
        return !$this->isEmpty();
    }
}

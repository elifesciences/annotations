<?php

namespace eLife\HypothesisClient\Result;

use Countable;
use Traversable;

interface Result extends CastsToArray, Countable, Traversable
{
    public function search(string $expression);
}

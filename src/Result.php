<?php

namespace eLife\HypothesisClient;

use Countable;
use Traversable;

interface Result extends CastsToArray, Countable, Traversable
{

    public function search(string $expression);
}

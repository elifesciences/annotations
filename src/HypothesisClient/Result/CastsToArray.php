<?php

namespace eLife\HypothesisClient\Result;

use ArrayAccess;

interface CastsToArray extends ArrayAccess
{
    public function toArray() : array;
}

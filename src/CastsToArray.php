<?php

namespace eLife\HypothesisClient;

use ArrayAccess;

interface CastsToArray extends ArrayAccess
{
    public function toArray() : array;
}

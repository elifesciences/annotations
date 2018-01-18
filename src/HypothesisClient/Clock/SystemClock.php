<?php

namespace eLife\HypothesisClient\Clock;

class SystemClock implements Clock
{
    public function time() : int
    {
        return time();
    }
}

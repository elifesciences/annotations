<?php

namespace eLife\HypothesisClient\Clock;

class FixedClock implements Clock
{
    private $fixed_time;

    public function __construct(int $time = null)
    {
        $this->fixed_time = $time ?? time();
    }

    public function time() : int
    {
        return $this->fixed_time;
    }
}

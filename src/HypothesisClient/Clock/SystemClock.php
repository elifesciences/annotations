<?php

namespace eLife\HypothesisClient\Clock;

final class SystemClock implements Clock
{
    public function time() : int
    {
        return time();
    }
}

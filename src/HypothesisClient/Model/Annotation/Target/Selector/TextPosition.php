<?php

namespace eLife\HypothesisClient\Model\Annotation\Target\Selector;

final class TextPosition
{
    private $start;
    private $end;

    /**
     * @internal
     */
    public function __construct(
        int $start,
        int $end
    ) {
        $this->start = $start;
        $this->end = $end;
    }

    public function getStart() : int
    {
        return $this->start;
    }

    public function getEnd() : int
    {
        return $this->end;
    }
}

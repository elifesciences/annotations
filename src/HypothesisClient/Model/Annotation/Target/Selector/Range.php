<?php

namespace eLife\HypothesisClient\Model\Annotation\Target\Selector;

final class Range
{
    private $startOffset;
    private $startContainer;
    private $endOffset;
    private $endContainer;

    /**
     * @internal
     */
    public function __construct(
        string $startContainer,
        string $endContainer,
        int $startOffset,
        int $endOffset
    ) {
        $this->startContainer = $startContainer;
        $this->endContainer = $endContainer;
        $this->startOffset = $startOffset;
        $this->endOffset = $endOffset;
    }

    public function getStartContainer() : string
    {
        return $this->startContainer;
    }

    public function getEndContainer() : string
    {
        return $this->endContainer;
    }

    public function getStartOffset() : int
    {
        return $this->startOffset;
    }

    public function getEndOffset() : int
    {
        return $this->endOffset;
    }
}

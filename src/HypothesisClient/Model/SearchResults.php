<?php

namespace eLife\HypothesisClient\Model;

final class SearchResults
{
    private $total;
    private $annotations;

    /**
     * @internal
     */
    public function __construct(int $total, array $annotations)
    {
        $this->total = $total;
        $this->annotations = $annotations;
    }

    public function getTotal() : int
    {
        return $this->total;
    }

    public function getAnnotations() : array
    {
        return $this->annotations;
    }
}

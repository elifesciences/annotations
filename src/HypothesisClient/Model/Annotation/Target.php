<?php

namespace eLife\HypothesisClient\Model\Annotation;

use eLife\HypothesisClient\Model\Annotation\Target\Selector;

final class Target
{
    private $source;
    private $selector;

    /**
     * @internal
     */
    public function __construct(
        string $source,
        Selector $selector = null
    ) {
        $this->source = $source;
        $this->selector = $selector;
    }

    public function getSource() : string
    {
        return $this->source;
    }

    /**
     * @return Selector|null
     */
    public function getSelector()
    {
        return $this->selector;
    }
}

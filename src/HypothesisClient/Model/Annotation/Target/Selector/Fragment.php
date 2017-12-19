<?php

namespace eLife\HypothesisClient\Model\Annotation\Target\Selector;

final class Fragment
{
    private $conformsTo;
    private $value;

    /**
     * @internal
     */
    public function __construct(
        string $conformsTo,
        string $value
    ) {
        $this->conformsTo = $conformsTo;
        $this->value = $value;
    }

    public function getConformsTo() : string
    {
        return $this->conformsTo;
    }

    public function getValue() : string
    {
        return $this->value;
    }
}

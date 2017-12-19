<?php

namespace eLife\HypothesisClient\Model\Annotation;

final class Permissions
{
    private $read;

    /**
     * @internal
     */
    public function __construct(
        string $read
    ) {
        $this->read = $read;
    }

    public function getRead() : string
    {
        return $this->read;
    }
}

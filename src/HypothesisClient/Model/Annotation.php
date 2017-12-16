<?php

namespace eLife\HypothesisClient\Model;

final class Annotation
{
    private $id;

    /**
     * @internal
     */
    public function __construct(
        string $id
    ) {
        $this->id = $id;
    }

    public function getId() : string
    {
        return $this->id;
    }
}

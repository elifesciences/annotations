<?php

namespace eLife\HypothesisClient\ApiSdk\Model;

final class Annotation implements Model, HasId
{
    private $id;

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

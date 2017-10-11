<?php

namespace eLife\HypothesisClient\ApiSdk\Model;

final class Annotation implements Model, HasId
{
    private $id;
    private $links;
    private $text;

    public function __construct(
        string $id,
        Links $links,
        $text = null
    ) {
        $this->id = $id;
        $this->links = $links;
        $this->text = $text;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getLinks() : Links
    {
        return $this->links;
    }

    /**
     * @return string|null
     */
    public function getText()
    {
        return $this->text;
    }
}

<?php

namespace eLife\HypothesisClient\Model\Annotation;

final class Document
{
    private $title;

    /**
     * @internal
     */
    public function __construct(
        string $title
    ) {
        $this->title = $title;
    }

    public function getTitle() : string
    {
        return $this->title;
    }
}

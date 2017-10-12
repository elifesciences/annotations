<?php

namespace eLife\HypothesisClient\ApiSdk\Model;

use DateTimeImmutable;

final class Annotation implements Model, HasId, HasPublishedDate, HasUpdatedDate
{
    private $id;
    private $published;
    private $updated;
    private $links;
    private $text;

    public function __construct(
        string $id,
        DateTimeImmutable $published,
        DateTimeImmutable $updated = null,
        Links $links,
        $text = null
    ) {
        $this->id = $id;
        $this->published = $published;
        $this->updated = $updated;
        $this->links = $links;
        $this->text = $text;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getPublishedDate() : DateTimeImmutable
    {
        return $this->published;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getUpdatedDate()
    {
        return $this->updated;
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

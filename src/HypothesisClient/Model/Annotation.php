<?php

namespace eLife\HypothesisClient\Model;

use DateTimeImmutable;
use eLife\HypothesisClient\Model\Annotation\Document;
use eLife\HypothesisClient\Model\Annotation\Permissions;
use eLife\HypothesisClient\Model\Annotation\Target;

final class Annotation
{
    const PUBLIC_GROUP = 'group:__world__';

    private $id;
    private $text;
    private $created;
    private $updated;
    private $document;
    private $uri;
    private $references;
    private $permissions;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        string $text = null,
        DateTimeImmutable $created,
        DateTimeImmutable $updated,
        Document $document,
        Target $target,
        string $uri,
        array $references,
        Permissions $permissions
    ) {
        $this->id = $id;
        $this->text = $text;
        $this->created = $created;
        $this->updated = $updated;
        $this->document = $document;
        $this->target = $target;
        $this->uri = $uri;
        $this->references = $references;
        $this->permissions = $permissions;
    }

    public function getId() : string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getText()
    {
        return $this->text;
    }

    public function getCreatedDate() : DateTimeImmutable
    {
        return $this->created;
    }

    public function getUpdatedDate() : DateTimeImmutable
    {
        return $this->updated;
    }

    public function getDocument() : Document
    {
        return $this->document;
    }

    public function getTarget() : Target
    {
        return $this->target;
    }

    public function getUri() : string
    {
        return $this->uri;
    }

    public function getReferences() : array
    {
        return $this->references;
    }

    public function getPermissions() : Permissions
    {
        return $this->permissions;
    }
}

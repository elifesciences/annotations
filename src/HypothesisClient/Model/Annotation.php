<?php

namespace eLife\HypothesisClient\Model;

use Assert\Assert;
use DateTimeImmutable;
use eLife\HypothesisClient\Model\Annotation\Document;
use eLife\HypothesisClient\Model\Annotation\Permissions;
use eLife\HypothesisClient\Model\Annotation\Target;

final class Annotation
{
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
     *
     * @param string            $id
     * @param string|null       $text
     * @param DateTimeImmutable $created
     * @param DateTimeImmutable $updated
     * @param Document          $document
     * @param Target            $target
     * @param string            $uri
     * @param array|null        $references
     * @param $permissions
     */
    public function __construct(
        string $id,
        $text,
        DateTimeImmutable $created,
        DateTimeImmutable $updated,
        Document $document,
        Target $target,
        string $uri,
        $references,
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
        $this->validate();
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

    /**
     * @return array|null
     */
    public function getReferences()
    {
        return $this->references;
    }

    public function getPermissions() : Permissions
    {
        return $this->permissions;
    }

    private function validate() : bool
    {
        return Assert::lazy()
            ->that(array_filter([$this->getText(), $this->getTarget()->getSelector() && $this->getTarget()->getSelector()->getTextQuote()]), 'Text or target text quote selector')
            ->notEmpty('at least one value must be present.')
            ->verifyNow();
    }
}

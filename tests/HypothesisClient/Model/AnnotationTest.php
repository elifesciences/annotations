<?php

namespace tests\eLife\HypothesisClient\Model;

use DateTimeImmutable;
use DateTimeZone;
use eLife\HypothesisClient\Model\Annotation;
use PHPUnit\Framework\TestCase;

/**
 * @covers \eLife\HypothesisClient\Model\Annotation
 */
final class AnnotationTest extends TestCase
{
    /** @var Annotation */
    private $annotation;
    /** @var DateTimeImmutable */
    private $created;
    /** @var Annotation\Document */
    private $document;
    /** @var Annotation\Permissions */
    private $permissions;
    /** @var Annotation\Target */
    private $target;
    /** @var DateTimeImmutable */
    private $updated;

    /**
     * @before
     */
    public function prepare_annotation()
    {
        $this->annotation = new Annotation(
            'id',
            'text',
            $this->created = new DateTimeImmutable('now', new DateTimeZone('Z')),
            $this->updated = new DateTimeImmutable('now', new DateTimeZone('Z')),
            $this->document = new Annotation\Document('title'),
            $this->target = new Annotation\Target('source'),
            'uri',
            [],
            $this->permissions = new Annotation\Permissions('read')
        );
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $this->assertSame('id', $this->annotation->getId());
    }

    /**
     * @test
     */
    public function it_may_have_text()
    {
        $without = new Annotation(
            'id',
            null,
            $this->created = new DateTimeImmutable('now', new DateTimeZone('Z')),
            $this->updated = new DateTimeImmutable('now', new DateTimeZone('Z')),
            $this->document = new Annotation\Document('title'),
            $this->target = new Annotation\Target(
                'source',
                new Annotation\Target\Selector(
                    new Annotation\Target\Selector\TextQuote('exact', 'prefix', 'suffix')
                )
            ),
            'uri',
            [],
            $this->permissions = new Annotation\Permissions('read')
        );
        $with = $this->annotation;
        $this->assertNull($without->getText());
        $this->assertSame('text', $with->getText());
    }

    /**
     * @test
     */
    public function it_has_a_created_date()
    {
        $this->assertSame($this->created, $this->annotation->getCreatedDate());
    }

    /**
     * @test
     */
    public function it_has_an_updated_date()
    {
        $this->assertSame($this->updated, $this->annotation->getUpdatedDate());
    }

    /**
     * @test
     */
    public function it_has_a_document()
    {
        $this->assertSame($this->document, $this->annotation->getDocument());
    }

    /**
     * @test
     */
    public function it_has_a_target()
    {
        $this->assertSame($this->target, $this->annotation->getTarget());
    }

    /**
     * @test
     */
    public function it_has_a_uri()
    {
        $this->assertSame('uri', $this->annotation->getUri());
    }

    /**
     * @test
     */
    public function it_has_permissions()
    {
        $this->assertSame($this->permissions, $this->annotation->getPermissions());
    }
}

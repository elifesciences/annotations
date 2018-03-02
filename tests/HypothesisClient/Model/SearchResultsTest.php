<?php

namespace tests\eLife\HypothesisClient\Model;

use DateTimeImmutable;
use DateTimeZone;
use eLife\HypothesisClient\Model\Annotation;
use eLife\HypothesisClient\Model\SearchResults;
use PHPUnit_Framework_TestCase;

final class SearchResultsTest extends PHPUnit_Framework_TestCase
{
    /** @var Annotation[] */
    private $annotations;
    /** @var SearchResults */
    private $searchResults;

    /**
     * @before
     */
    public function prepareResults()
    {
        $this->annotations = [
            new Annotation(
                'id',
                'text',
                new DateTimeImmutable('now', new DateTimeZone('Z')),
                new DateTimeImmutable('now', new DateTimeZone('Z')),
                new Annotation\Document('title'),
                new Annotation\Target('source'),
                'uri',
                [],
                new Annotation\Permissions('read')
            ),
        ];
        $this->searchResults = new SearchResults(3, $this->annotations);
    }

    /**
     * @test
     */
    public function it_has_a_total()
    {
        $this->assertSame(3, $this->searchResults->getTotal());
    }

    /**
     * @test
     */
    public function it_has_annotations()
    {
        $this->assertEquals($this->annotations, $this->searchResults->getAnnotations());
    }
}

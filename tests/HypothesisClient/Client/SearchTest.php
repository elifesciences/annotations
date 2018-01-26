<?php

namespace tests\eLife\HypothesisClient\Client;

use DateTimeImmutable;
use eLife\HypothesisClient\ApiClient\SearchClient;
use eLife\HypothesisClient\Client\Search;
use eLife\HypothesisClient\HttpClient\HttpClient;
use eLife\HypothesisClient\Model\Annotation;
use eLife\HypothesisClient\Model\SearchResults;
use eLife\HypothesisClient\Result\ArrayResult;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use tests\eLife\HypothesisClient\RequestConstraint;

/**
 * @covers \eLife\HypothesisClient\Client\Search
 */
class SearchTest extends PHPUnit_Framework_TestCase
{
    private $denormalizer;
    private $group;
    private $httpClient;
    /** @var Search */
    private $search;
    /** @var SearchClient */
    private $searchClient;

    /**
     * @before
     */
    public function prepareDependencies()
    {
        $this->group = 'group';
        $this->denormalizer = $this->getMockBuilder(DenormalizerInterface::class)
            ->setMethods(['denormalize', 'supportsDenormalization'])
            ->getMock();
        $this->httpClient = $this->getMockBuilder(HttpClient::class)
            ->setMethods(['send'])
            ->getMock();
        $this->searchClient = new SearchClient($this->httpClient, $this->group);
        $this->search = new Search($this->searchClient, $this->denormalizer);
    }

    /**
     * @test
     */
    public function it_will_list_annotations()
    {
        $created = '2017-12-29T17:02:56.346939+00:00';
        $updated = '2017-12-29T18:02:56.359872+00:00';
        $request = new Request(
            'GET',
            'search?user=username&group=group&offset=0&limit=20&order=desc&sort=updated',
            ['User-Agent' => 'HypothesisClient']
        );
        $rows = [
            [
                'id' => 'identifier1',
                'text' => 'text',
                'created' => $created,
                'updated' => $updated,
                'document' => [
                    'title' => [
                        'title1',
                    ],
                ],
                'target' => [
                    [
                        'source' => 'source1',
                    ],
                ],
                'uri' => 'uri1',
                'permissions' => [
                    'read' => [
                        'read',
                    ],
                ],
            ],
            [
                'id' => 'identifier2',
                'created' => $created,
                'updated' => $updated,
                'document' => [
                    'title' => [
                        'title2',
                    ],
                ],
                'target' => [
                    [
                        'source' => 'source2',
                        'selector' => [
                            [
                                'type' => 'RangeSelector',
                                'startContainer' => 'start_container',
                                'endContainer' => 'end_container',
                                'startOffset' => 0,
                                'endOffset' => 10,
                            ],
                            [
                                'type' => 'TextPositionSelector',
                                'start' => 0,
                                'end' => 10,
                            ],
                            [
                                'type' => 'TextQuoteSelector',
                                'exact' => 'exact',
                                'prefix' => 'prefix',
                                'suffix' => 'suffix',
                            ],
                            [
                                'type' => 'FragmentSelector',
                                'conformsTo' => 'conforms_to',
                                'value' => 'value',
                            ],
                        ],
                    ],
                ],
                'references' => [
                    'ancestor1',
                    'ancestor2',
                ],
                'uri' => 'uri1',
                'permissions' => [
                    'read' => [
                        'read',
                    ],
                ],
            ],
        ];
        $response = new FulfilledPromise(new ArrayResult(
            [
                'total' => 100,
                'rows' => $rows,
            ]
        ));
        $annotations = [
            new Annotation(
                'identifier1',
                'text',
                new DateTimeImmutable($created),
                new DateTimeImmutable($created),
                new Annotation\Document('title1'),
                new Annotation\Target('source1'),
                'uri1',
                null,
                new Annotation\Permissions('read')
            ),
            new Annotation(
                'identifier2',
                null,
                new DateTimeImmutable($created),
                new DateTimeImmutable($updated),
                new Annotation\Document('title2'),
                new Annotation\Target(
                    'source2',
                    new Annotation\Target\Selector(
                        new Annotation\Target\Selector\TextQuote('exact', 'prefix', 'suffix')
                    )
                ),
                'uri2',
                [
                    'ancestor1',
                    'ancestor2',
                ],
                new Annotation\Permissions('read')
            ),
        ];

        $this->denormalizer
            ->expects($this->at(0))
            ->method('denormalize')
            ->with($rows[0], Annotation::class)
            ->willReturn($annotations[0]);
        $this->denormalizer
            ->expects($this->at(1))
            ->method('denormalize')
            ->with($rows[1], Annotation::class)
            ->willReturn($annotations[1]);
        $this->httpClient
            ->expects($this->once())
            ->method('send')
            ->with(RequestConstraint::equalTo($request))
            ->willReturn($response);
        $query = $this->search->query('username', null, 0, 20, true, 'updated')->wait();
        $this->assertEquals(new SearchResults(100, $annotations), $query);
    }
}

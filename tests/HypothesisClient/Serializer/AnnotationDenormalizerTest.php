<?php

namespace tests\eLife\HypothesisClient\Serializer;

use DateTimeImmutable;
use eLife\HypothesisClient\Model\Annotation;
use eLife\HypothesisClient\Serializer\Annotation\DocumentDenormalizer;
use eLife\HypothesisClient\Serializer\Annotation\PermissionsDenormalizer;
use eLife\HypothesisClient\Serializer\Annotation\Target\Selector\FragmentDenormalizer;
use eLife\HypothesisClient\Serializer\Annotation\Target\Selector\RangeDenormalizer;
use eLife\HypothesisClient\Serializer\Annotation\Target\Selector\TextPositionDenormalizer;
use eLife\HypothesisClient\Serializer\Annotation\Target\Selector\TextQuoteDenormalizer;
use eLife\HypothesisClient\Serializer\Annotation\Target\SelectorDenormalizer;
use eLife\HypothesisClient\Serializer\Annotation\TargetDenormalizer;
use eLife\HypothesisClient\Serializer\AnnotationDenormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * @covers \eLife\HypothesisClient\Serializer\AnnotationDenormalizer
 */
final class AnnotationDenormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var AnnotationDenormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new AnnotationDenormalizer();

        new Serializer([
            $this->normalizer,
            new DocumentDenormalizer(),
            new FragmentDenormalizer(),
            new PermissionsDenormalizer(),
            new RangeDenormalizer(),
            new SelectorDenormalizer(),
            new TargetDenormalizer(),
            new TextPositionDenormalizer(),
            new TextQuoteDenormalizer(),
        ]);
    }

    /**
     * @test
     */
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    /**
     * @test
     * @dataProvider canDenormalizeProvider
     */
    public function it_can_denormalize_annotations($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'annotation' => [[], Annotation::class, [], true],
            'non-annotation' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_will_denormalize_annotations(array $json, Annotation $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, Annotation::class));
    }

    public function denormalizeProvider() : array
    {
        $created = '2017-12-29T17:02:56.346939+00:00';
        $updated = '2017-12-29T18:02:56.359872+00:00';

        return [
            'complete' => [
                [
                    'id' => 'identifier',
                    'text' => 'text',
                    'created' => $created,
                    'updated' => $updated,
                    'document' => [
                        'title' => [
                            'title',
                        ],
                    ],
                    'target' => [
                        [
                            'source' => 'source',
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
                    'uri' => 'uri',
                    'references' => [
                        'parent1',
                        'parent2',
                    ],
                    'permissions' => [
                        'read' => [
                            'read',
                        ],
                    ],
                ],
                new Annotation(
                    'identifier',
                    'text',
                    new DateTimeImmutable($created),
                    new DateTimeImmutable($updated),
                    new Annotation\Document('title'),
                    new Annotation\Target(
                        'source',
                        new Annotation\Target\Selector(
                            new Annotation\Target\Selector\TextQuote('exact', 'prefix', 'suffix'),
                            new Annotation\Target\Selector\TextPosition(0, 10),
                            new Annotation\Target\Selector\Range('start_container', 'end_container', 0, 10),
                            new Annotation\Target\Selector\Fragment('conforms_to', 'value')
                        )
                    ),
                    'uri',
                    ['parent1', 'parent2'],
                    new Annotation\Permissions('read')
                ),
            ],
            'no-text' => [
                [
                    'id' => 'identifier',
                    'created' => $created,
                    'updated' => $updated,
                    'document' => [
                        'title' => [
                            'title',
                        ],
                    ],
                    'target' => [
                        [
                            'source' => 'source',
                            'selector' => [
                                [
                                    'type' => 'TextQuoteSelector',
                                    'exact' => 'exact',
                                    'prefix' => 'prefix',
                                    'suffix' => 'suffix',
                                ],
                            ],
                        ],
                    ],
                    'uri' => 'uri',
                    'references' => [
                        'parent1',
                        'parent2',
                    ],
                    'permissions' => [
                        'read' => [
                            'read',
                        ],
                    ],
                ],
                new Annotation(
                    'identifier',
                    null,
                    new DateTimeImmutable($created),
                    new DateTimeImmutable($updated),
                    new Annotation\Document('title'),
                    new Annotation\Target(
                        'source',
                        new Annotation\Target\Selector(
                            new Annotation\Target\Selector\TextQuote('exact', 'prefix', 'suffix')
                        )
                    ),
                    'uri',
                    ['parent1', 'parent2'],
                    new Annotation\Permissions('read')
                ),
            ],
            'minimum' => [
                [
                    'id' => 'identifier',
                    'text' => 'text',
                    'created' => $created,
                    'updated' => $updated,
                    'document' => [
                        'title' => [
                            'title',
                        ],
                    ],
                    'target' => [
                        [
                            'source' => 'source',
                        ],
                    ],
                    'uri' => 'uri',
                    'permissions' => [
                        'read' => [
                            'read',
                        ],
                    ],
                ],
                new Annotation(
                    'identifier',
                    'text',
                    new DateTimeImmutable($created),
                    new DateTimeImmutable($updated),
                    new Annotation\Document('title'),
                    new Annotation\Target(
                        'source'
                    ),
                    'uri',
                    null,
                    new Annotation\Permissions('read')
                ),
            ],
        ];
    }
}

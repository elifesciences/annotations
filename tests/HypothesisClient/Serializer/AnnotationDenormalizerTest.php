<?php

namespace tests\eLife\HypothesisClient\Serializer;

use DateTimeImmutable;
use eLife\HypothesisClient\Model\Annotation;
use eLife\HypothesisClient\Serializer\Annotation\DocumentDenormalizer;
use eLife\HypothesisClient\Serializer\Annotation\PermissionsDenormalizer;
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
            new PermissionsDenormalizer(),
            new SelectorDenormalizer(),
            new TargetDenormalizer(),
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
                    'id' => 'Ng1e2sTBEeegJt8a9q3zpQ',
                    'text' => '<p>A new human species</p>',
                    'created' => $created,
                    'updated' => $updated,
                    'document' => [
                        'title' => [
                            'Human Evolution: The many mysteries of Homo naledi',
                        ],
                    ],
                    'target' => [
                        [
                            'source' => 'source',
                            'selector' => [
                                [
                                    'type' => 'RangeSelector',
                                    'startContainer' => '/div[4]',
                                    'endContainer' => '/div[4]',
                                    'startOffset' => 100,
                                    'endOffset' => 200,
                                ],
                                [
                                    'type' => 'TextPositionSelector',
                                    'start' => 10000,
                                    'end' => 10021,
                                ],
                                [
                                    'type' => 'TextQuoteSelector',
                                    'exact' => 'a new human species',
                                    'prefix' => 'have been assigned to ',
                                    'suffix' => ', Homo naledi',
                                ],
                                [
                                    'type' => 'FragmentSelector',
                                    'conformsTo' => 'https://tools.ietf.org/html/rfc3236',
                                    'value' => 'abstract',
                                ],
                            ],
                        ],
                    ],
                    'uri' => 'https://elifesciences.org/articles/10627',
                    'references' => [
                        'ancestor1',
                        'ancestor2',
                    ],
                    'permissions' => [
                        'read' => [
                            'group:__world__',
                        ],
                    ],
                ],
                new Annotation(
                    'Ng1e2sTBEeegJt8a9q3zpQ',
                    '<p>A new human species</p>',
                    new DateTimeImmutable($created),
                    new DateTimeImmutable($updated),
                    new Annotation\Document('Human Evolution: The many mysteries of Homo naledi'),
                    new Annotation\Target(
                        'source',
                        new Annotation\Target\Selector(
                            new Annotation\Target\Selector\TextQuote('a new human species', 'have been assigned to ', ', Homo naledi')
                        )
                    ),
                    'https://elifesciences.org/articles/10627',
                    ['ancestor1', 'ancestor2'],
                    new Annotation\Permissions('group:__world__')
                ),
            ],
            'no-text' => [
                [
                    'id' => 'OFdkMTBEeeIFd8-JnIE1wN',
                    'created' => $created,
                    'updated' => $updated,
                    'document' => [
                        'title' => [
                            'Human Evolution: The many mysteries of Homo naledi',
                        ],
                    ],
                    'target' => [
                        [
                            'source' => 'source',
                            'selector' => [
                                [
                                    'type' => 'TextQuoteSelector',
                                    'exact' => 'a new human species',
                                    'prefix' => 'have been assigned to ',
                                    'suffix' => ', Homo naledi',
                                ],
                            ],
                        ],
                    ],
                    'uri' => 'https://elifesciences.org/articles/10627',
                    'references' => [
                        'ancestor1',
                        'ancestor2',
                    ],
                    'permissions' => [
                        'read' => [
                            'group:__world__',
                        ],
                    ],
                ],
                new Annotation(
                    'OFdkMTBEeeIFd8-JnIE1wN',
                    null,
                    new DateTimeImmutable($created),
                    new DateTimeImmutable($updated),
                    new Annotation\Document('Human Evolution: The many mysteries of Homo naledi'),
                    new Annotation\Target(
                        'source',
                        new Annotation\Target\Selector(
                            new Annotation\Target\Selector\TextQuote('a new human species', 'have been assigned to ', ', Homo naledi')
                        )
                    ),
                    'https://elifesciences.org/articles/10627',
                    ['ancestor1', 'ancestor2'],
                    new Annotation\Permissions('group:__world__')
                ),
            ],
            'whitespace only' => [
                [
                    'id' => 'Ng1e2sTBEeegJt8a9q3zpQ',
                    'text' => 'An annotation',
                    'created' => $created,
                    'updated' => $updated,
                    'document' => [
                        'title' => [
                            'Human Evolution: The many mysteries of Homo naledi',
                        ],
                    ],
                    'target' => [
                        [
                            'source' => 'source',
                            'selector' => [
                                [
                                    'type' => 'RangeSelector',
                                    'startContainer' => '/div[4]',
                                    'endContainer' => '/div[4]',
                                    'startOffset' => 100,
                                    'endOffset' => 200,
                                ],
                                [
                                    'type' => 'TextPositionSelector',
                                    'start' => 10000,
                                    'end' => 10021,
                                ],
                                [
                                    'type' => 'TextQuoteSelector',
                                    'exact' => ' ',
                                    'prefix' => 'have been assigned to ',
                                    'suffix' => ', Homo naledi',
                                ],
                                [
                                    'type' => 'FragmentSelector',
                                    'conformsTo' => 'https://tools.ietf.org/html/rfc3236',
                                    'value' => 'abstract',
                                ],
                            ],
                        ],
                    ],
                    'uri' => 'https://elifesciences.org/articles/10627',
                    'references' => [
                        'ancestor1',
                        'ancestor2',
                    ],
                    'permissions' => [
                        'read' => [
                            'group:__world__',
                        ],
                    ],
                ],
                new Annotation(
                    'Ng1e2sTBEeegJt8a9q3zpQ',
                    'An annotation',
                    new DateTimeImmutable($created),
                    new DateTimeImmutable($updated),
                    new Annotation\Document('Human Evolution: The many mysteries of Homo naledi'),
                    new Annotation\Target('source'),
                    'https://elifesciences.org/articles/10627',
                    ['ancestor1', 'ancestor2'],
                    new Annotation\Permissions('group:__world__')
                ),
            ],
            'minimum' => [
                [
                    'id' => 'M_FoqMTBEeerwYvINYO67Q',
                    'text' => 'text',
                    'created' => $created,
                    'updated' => $updated,
                    'document' => [
                        'title' => [
                            'Human Evolution: The many mysteries of Homo naledi',
                        ],
                    ],
                    'target' => [
                        [
                            'source' => 'https://elifesciences.org/articles/10627',
                        ],
                    ],
                    'uri' => 'https://elifesciences.org/articles/10627',
                    'permissions' => [
                        'read' => [
                            'group:__world__',
                        ],
                    ],
                ],
                new Annotation(
                    'M_FoqMTBEeerwYvINYO67Q',
                    'text',
                    new DateTimeImmutable($created),
                    new DateTimeImmutable($updated),
                    new Annotation\Document('Human Evolution: The many mysteries of Homo naledi'),
                    new Annotation\Target(
                        'https://elifesciences.org/articles/10627'
                    ),
                    'https://elifesciences.org/articles/10627',
                    [],
                    new Annotation\Permissions('group:__world__')
                ),
            ],
        ];
    }
}

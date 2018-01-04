<?php

namespace tests\eLife\Annotations\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\Annotations\Serializer\AnnotationNormalizer;
use eLife\HypothesisClient\Model\Annotation;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @covers \eLife\Annotations\Serializer\AnnotationNormalizer
 */
final class AnnotationNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var AnnotationNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new AnnotationNormalizer();
    }

    /**
     * @test
     */
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    /**
     * @test
     * @dataProvider canNormalizeProvider
     */
    public function it_can_normalize_annotations($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $annotation = new Annotation(
            'id',
            'text',
            new DateTimeImmutable('now', new DateTimeZone('Z')),
            new DateTimeImmutable('now', new DateTimeZone('Z')),
            new Annotation\Document('title'),
            new Annotation\Target('source'),
            'uri',
            null,
            new Annotation\Permissions('read')
        );

        return [
            'annotation' => [$annotation, null, true],
            'non-annotation' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_will_normalize_annotations(array $expected, Annotation $annotation)
    {
        $this->assertSame($expected, $this->normalizer->normalize($annotation));
    }

    public function normalizeProvider() : array
    {
        $createdDate = '2017-11-29T17:41:28Z';
        $updatedDate = '2018-01-04T11:23:47Z';
        return [
            'minimum' => [
                [
                    'id' => 'id',
                    'access' => 'public',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ]
                    ],
                    'created' => $createdDate,
                    'document' => [
                        'title' => 'title',
                        'uri' => 'uri',
                    ],
                    'parents' => [],
                ],
                new Annotation(
                    'id',
                    'text',
                    new DateTimeImmutable($createdDate),
                    new DateTimeImmutable($createdDate),
                    new Annotation\Document('title'),
                    new Annotation\Target('source'),
                    'uri',
                    null,
                    new Annotation\Permissions(Annotation::PUBLIC_GROUP)
                ),
            ],
        ];
    }
}

<?php

namespace tests\eLife\HypothesisClient\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\HypothesisClient\ApiSdk\ApiSdk;
use eLife\HypothesisClient\ApiSdk\Model\Annotation;
use eLife\HypothesisClient\ApiSdk\Model\Links;
use eLife\HypothesisClient\ApiSdk\Serializer\AnnotationNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use tests\eLife\HypothesisClient\ApiSdk\TestCase;

final class AnnotationNormalizerTest extends TestCase
{
    /** @var AnnotationNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new AnnotationNormalizer();

        new Serializer([$this->normalizer]);
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
        $annotation = new Annotation('id', new DateTimeImmutable('now', new DateTimeZone('Z')), null, new Links('http://url.incontext'));

        return [
            'annotation' => [$annotation, null, true],
            'annotation with format' => [$annotation, 'foo', true],
            'non-annotation' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_annotations(Annotation $annotation, array $expected)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($annotation));
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
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_annotations(Annotation $expected, array $json)
    {
        $actual = $this->normalizer->denormalize($json, Annotation::class);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        $date = new DateTimeImmutable('yesterday', new DateTimeZone('Z'));
        $updatedDate = new DateTimeImmutable('now', new DateTimeZone('Z'));
        return [
            'complete' => [
                new Annotation('id', $date, $updatedDate, new Links('http://url.incontext', 'http://url.json', 'http://url.html'), 'text'),
                [
                    'id' => 'id',
                    'created' => $date->format(ApiSdk::DATE_FORMAT),
                    'updated' => $updatedDate->format(ApiSdk::DATE_FORMAT),
                    'links' => [
                        'incontext' => 'http://url.incontext',
                        'json' => 'http://url.json',
                        'html' => 'http://url.html',
                    ],
                    'text' => 'text',
                ],
            ],
        ];
    }
}

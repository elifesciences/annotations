<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\HypothesisClient\ApiSdk\Model\Annotation;
use eLife\HypothesisClient\ApiSdk\Serializer\AnnotationNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use test\eLife\HypothesisClient\ApiSdk\TestCase;

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
        $annualReport = new Annotation('id');

        return [
            'annotation' => [$annualReport, null, true],
            'annotation with format' => [$annualReport, 'foo', true],
            'non-annotation' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_annual_reports(Annotation $annotation, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($annotation));
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
            'non-annual report' => [[], get_class($this), [], false],
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
        return [
            'complete' => [
                new Annotation('id'),
                [
                    'id' => 'id',
                ],
            ],
        ];
    }
}

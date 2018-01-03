<?php

namespace tests\eLife\HypothesisClient\Serializer\Annotation\Target\Selector;

use eLife\HypothesisClient\Model\Annotation\Target\Selector\Range;
use eLife\HypothesisClient\Serializer\Annotation\Target\Selector\RangeNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @covers \eLife\HypothesisClient\Serializer\Annotation\Target\Selector\RangeNormalizer
 */
final class RangeNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var RangeNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new RangeNormalizer();
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
    public function it_can_denormalize_ranges($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'range' => [[], Range::class, [], true],
            'non-range' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_will_denormalize_ranges(array $json, Range $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, Range::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'startContainer' => 'start_container',
                    'endContainer' => 'end_container',
                    'startOffset' => 10,
                    'endOffset' => 101,
                ],
                new Range('start_container', 'end_container', 10, 101),
            ],
        ];
    }
}

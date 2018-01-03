<?php

namespace tests\eLife\HypothesisClient\Serializer\Annotation\Target\Selector;

use eLife\HypothesisClient\Model\Annotation\Target\Selector\TextPosition;
use eLife\HypothesisClient\Serializer\Annotation\Target\Selector\TextPositionNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @covers \eLife\HypothesisClient\Serializer\Annotation\Target\Selector\TextPositionNormalizer
 */
final class TextPositionNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var TextPositionNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new TextPositionNormalizer();
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
    public function it_can_denormalize_text_positions($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'text-position' => [[], TextPosition::class, [], true],
            'non-text-position' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_will_denormalize_text_positions(array $json, TextPosition $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, TextPosition::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'start' => 10,
                    'end' => 101,
                ],
                new TextPosition(10, 101),
            ],
        ];
    }
}

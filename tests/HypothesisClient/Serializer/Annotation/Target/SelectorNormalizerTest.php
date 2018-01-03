<?php

namespace tests\eLife\HypothesisClient\Serializer\Annotation\Target;

use eLife\HypothesisClient\Model\Annotation\Target\Selector;
use eLife\HypothesisClient\Serializer\Annotation\Target\Selector\FragmentNormalizer;
use eLife\HypothesisClient\Serializer\Annotation\Target\Selector\RangeNormalizer;
use eLife\HypothesisClient\Serializer\Annotation\Target\Selector\TextPositionNormalizer;
use eLife\HypothesisClient\Serializer\Annotation\Target\Selector\TextQuoteNormalizer;
use eLife\HypothesisClient\Serializer\Annotation\Target\SelectorNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * @covers \eLife\HypothesisClient\Serializer\Annotation\Target\SelectorNormalizer
 */
final class SelectorNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var SelectorNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new SelectorNormalizer();

        new Serializer([
            $this->normalizer,
            new FragmentNormalizer(),
            new RangeNormalizer(),
            new TextPositionNormalizer(),
            new TextQuoteNormalizer(),
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
    public function it_can_denormalize_selectors($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'selector' => [[], Selector::class, [], true],
            'non-selector' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_will_denormalize_selectors(array $json, Selector $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, Selector::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
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
                new Selector(
                    new Selector\Range('start_container', 'end_container', 0, 10),
                    new Selector\TextPosition(0, 10),
                    new Selector\TextQuote('exact', 'prefix', 'suffix'),
                    new Selector\Fragment('conforms_to', 'value')
                ),
            ],
            'minimum' => [
                [
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
                ],
                new Selector(
                    new Selector\Range('start_container', 'end_container', 0, 10),
                    new Selector\TextPosition(0, 10),
                    new Selector\TextQuote('exact', 'prefix', 'suffix')
                ),
            ],
        ];
    }
}
<?php

namespace tests\eLife\HypothesisClient\Serializer\Annotation;

use eLife\HypothesisClient\Model\Annotation\Target;
use eLife\HypothesisClient\Serializer\Annotation\Target\Selector\TextQuoteDenormalizer;
use eLife\HypothesisClient\Serializer\Annotation\Target\SelectorDenormalizer;
use eLife\HypothesisClient\Serializer\Annotation\TargetDenormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * @covers \eLife\HypothesisClient\Serializer\Annotation\TargetDenormalizer
 */
final class TargetDenormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var TargetDenormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new TargetDenormalizer();

        new Serializer([
            $this->normalizer,
            new SelectorDenormalizer(),
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
    public function it_can_denormalize_targets($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'target' => [[], Target::class, [], true],
            'non-target' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_will_denormalize_targets(array $json, Target $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, Target::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
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
                new Target(
                    'source',
                    new Target\Selector(
                        new Target\Selector\TextQuote('exact', 'prefix', 'suffix')
                    )
                ),
            ],
            'minimum' => [
                [
                    'source' => 'source',
                ],
                new Target('source'),
            ],
        ];
    }
}

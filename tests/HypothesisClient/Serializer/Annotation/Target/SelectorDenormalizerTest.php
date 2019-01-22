<?php

namespace tests\eLife\HypothesisClient\Serializer\Annotation\Target;

use eLife\HypothesisClient\Model\Annotation\Target\Selector;
use eLife\HypothesisClient\Serializer\Annotation\Target\Selector\TextQuoteDenormalizer;
use eLife\HypothesisClient\Serializer\Annotation\Target\SelectorDenormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * @covers \eLife\HypothesisClient\Serializer\Annotation\Target\SelectorDenormalizer
 */
final class SelectorDenormalizerTest extends TestCase
{
    /** @var SelectorDenormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new SelectorDenormalizer();

        new Serializer([
            $this->normalizer,
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
                new Selector(
                    new Selector\TextQuote('a new human species', 'have been assigned to ', ', Homo naledi')
                ),
            ],
            'minimum' => [
                [
                    [
                        'type' => 'TextQuoteSelector',
                        'exact' => 'a new human species',
                        'prefix' => 'have been assigned to ',
                        'suffix' => ', Homo naledi',
                    ],
                ],
                new Selector(
                    new Selector\TextQuote('a new human species', 'have been assigned to ', ', Homo naledi')
                ),
            ],
        ];
    }
}

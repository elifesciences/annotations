<?php

namespace tests\eLife\HypothesisClient\Serializer\Annotation;

use eLife\HypothesisClient\Model\Annotation\Target;
use eLife\HypothesisClient\Serializer\Annotation\Target\Selector\TextQuoteDenormalizer;
use eLife\HypothesisClient\Serializer\Annotation\Target\SelectorDenormalizer;
use eLife\HypothesisClient\Serializer\Annotation\TargetDenormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * @covers \eLife\HypothesisClient\Serializer\Annotation\TargetDenormalizer
 */
final class TargetDenormalizerTest extends TestCase
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
                    'source' => 'https://elifesciences.org/articles/10627',
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
                new Target(
                    'https://elifesciences.org/articles/10627',
                    new Target\Selector(
                        new Target\Selector\TextQuote('a new human species', 'have been assigned to ', ', Homo naledi')
                    )
                ),
            ],
            'minimum' => [
                [
                    'source' => 'https://elifesciences.org/articles/10627',
                ],
                new Target('https://elifesciences.org/articles/10627'),
            ],
        ];
    }
}

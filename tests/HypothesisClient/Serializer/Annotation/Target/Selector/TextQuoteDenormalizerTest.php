<?php

namespace tests\eLife\HypothesisClient\Serializer\Annotation\Target\Selector;

use eLife\HypothesisClient\Model\Annotation\Target\Selector\TextQuote;
use eLife\HypothesisClient\Serializer\Annotation\Target\Selector\TextQuoteDenormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @covers \eLife\HypothesisClient\Serializer\Annotation\Target\Selector\TextQuoteDenormalizer
 */
final class TextQuoteDenormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var TextQuoteDenormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new TextQuoteDenormalizer();
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
    public function it_can_denormalize_text_quotes($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'text-quote' => [[], TextQuote::class, [], true],
            'non-text-quote' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_will_denormalize_text_quotes(array $json, TextQuote $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, TextQuote::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'exact' => 'a new human species',
                    'prefix' => 'have been assigned to ',
                    'suffix' => ', Homo naledi',
                ],
                new TextQuote('a new human species', 'have been assigned to ', ', Homo naledi')
            ],
        ];
    }
}

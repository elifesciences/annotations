<?php

namespace tests\eLife\HypothesisClient\Serializer\Annotation\Target\Selector;

use eLife\HypothesisClient\Model\Annotation\Target\Selector\Fragment;
use eLife\HypothesisClient\Serializer\Annotation\Target\Selector\FragmentDenormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @covers \eLife\HypothesisClient\Serializer\Annotation\Target\Selector\FragmentDenormalizer
 */
final class FragmentDenormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var FragmentDenormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new FragmentDenormalizer();
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
    public function it_can_denormalize_fragments($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'fragment' => [[], Fragment::class, [], true],
            'non-fragment' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_will_denormalize_fragments(array $json, Fragment $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, Fragment::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'conformsTo' => 'conforms_to',
                    'value' => 'value',
                ],
                new Fragment('conforms_to', 'value'),
            ],
        ];
    }
}

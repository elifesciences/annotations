<?php

namespace tests\eLife\HypothesisClient\Serializer\Annotation;

use eLife\HypothesisClient\Model\Annotation\Document;
use eLife\HypothesisClient\Serializer\Annotation\DocumentDenormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @covers \eLife\HypothesisClient\Serializer\Annotation\DocumentDenormalizer
 */
final class DocumentDenormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var DocumentDenormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new DocumentDenormalizer();
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
    public function it_can_denormalize_documents($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'document' => [[], Document::class, [], true],
            'non-document' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_will_denormalize_documents(array $json, Document $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, Document::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'title' => [
                        'Human Evolution: The many mysteries of Homo naledi',
                    ],
                ],
                new Document('Human Evolution: The many mysteries of Homo naledi'),
            ],
            'minimum' => [
                [],
                new Document('No title available'),
            ],
        ];
    }
}

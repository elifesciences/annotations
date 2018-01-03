<?php

namespace tests\eLife\HypothesisClient\Serializer;

use eLife\HypothesisClient\Model\Token;
use eLife\HypothesisClient\Serializer\TokenNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @covers \eLife\HypothesisClient\Serializer\TokenNormalizer
 */
final class TokenNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var TokenNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new TokenNormalizer();
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
    public function it_can_denormalize_tokens($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'token' => [[], Token::class, [], true],
            'non-token' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_will_denormalize_tokens(array $json, Token $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, Token::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'access_token' => 'access_token',
                    'token_type' => 'token_type',
                    'expires_in' => 1000.99,
                    'refresh_token' => 'refresh_token',
                ],
                new Token('access_token', 'token_type', 1000.99, 'refresh_token'),
            ],
        ];
    }
}

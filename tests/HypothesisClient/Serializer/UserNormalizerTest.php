<?php

namespace tests\eLife\HypothesisClient\Serializer;

use eLife\HypothesisClient\Model\User;
use eLife\HypothesisClient\Serializer\UserNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @covers \eLife\HypothesisClient\Serializer\UserNormalizer
 */
final class UserNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var UserNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new UserNormalizer();
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
    public function it_can_denormalize_users($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'user' => [[], User::class, [], true],
            'non-user' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_will_denormalize_users(array $json, User $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, User::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'username' => 'username',
                    'email' => 'email@email.com',
                    'display_name' => 'display_name',
                    'new' => true,
                ],
                new User('username', 'email@email.com', 'display_name', true),
            ],
            'no-email' => [
                [
                    'username' => 'username',
                    'display_name' => 'display_name',
                    'new' => true,
                ],
                new User('username', null, 'display_name', true),
            ],
            'no-display-name' => [
                [
                    'username' => 'username',
                    'email' => 'email@email.com',
                    'new' => true,
                ],
                new User('username', 'email@email.com', null, true),
            ],
            'minimum' => [
                [
                    'username' => 'username',
                    'email' => 'email@email.com',
                    'display_name' => 'display_name',
                ],
                new User('username', 'email@email.com', 'display_name'),
            ],
        ];
    }
}

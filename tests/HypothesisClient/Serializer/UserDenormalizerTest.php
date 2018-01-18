<?php

namespace tests\eLife\HypothesisClient\Serializer;

use eLife\HypothesisClient\Model\User;
use eLife\HypothesisClient\Serializer\UserDenormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @covers \eLife\HypothesisClient\Serializer\UserDenormalizer
 */
final class UserDenormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var UserDenormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new UserDenormalizer();
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
                    'username' => 'jcarberry',
                    'email' => 'email@email.com',
                    'display_name' => 'J. Carberry',
                    'new' => true,
                ],
                new User('jcarberry', 'email@email.com', 'J. Carberry', true),
            ],
            'no-email' => [
                [
                    'username' => 'jcarberry',
                    'display_name' => 'J. Carberry',
                    'new' => true,
                ],
                new User('jcarberry', null, 'J. Carberry', true),
            ],
            'no-display-name' => [
                [
                    'username' => 'jcarberry',
                    'email' => 'email@email.com',
                    'new' => true,
                ],
                new User('jcarberry', 'email@email.com', null, true),
            ],
            'minimum' => [
                [
                    'username' => 'jcarberry',
                    'email' => 'email@email.com',
                    'display_name' => 'J. Carberry',
                ],
                new User('jcarberry', 'email@email.com', 'J. Carberry'),
            ],
        ];
    }
}

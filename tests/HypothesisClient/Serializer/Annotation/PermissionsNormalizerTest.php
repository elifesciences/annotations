<?php

namespace tests\eLife\HypothesisClient\Serializer\Annotation;

use eLife\HypothesisClient\Model\Annotation\Permissions;
use eLife\HypothesisClient\Serializer\Annotation\PermissionsNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @covers \eLife\HypothesisClient\Serializer\Annotation\PermissionsNormalizer
 */
final class PermissionsNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var PermissionsNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new PermissionsNormalizer();
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
    public function it_can_denormalize_permissions($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'permissions' => [[], Permissions::class, [], true],
            'non-permissions' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_will_denormalize_permissions(array $json, Permissions $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, Permissions::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'read' => [
                        'read',
                    ],
                ],
                new Permissions('read'),
            ],
        ];
    }
}

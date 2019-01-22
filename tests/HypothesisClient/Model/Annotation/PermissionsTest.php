<?php

namespace tests\eLife\HypothesisClient\Model\Annotation;

use eLife\HypothesisClient\Model\Annotation\Permissions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \eLife\HypothesisClient\Model\Annotation\Permissions
 */
final class PermissionsTest extends TestCase
{
    /** @var Permissions */
    private $permissions;

    /**
     * @before
     */
    public function prepare_permissions()
    {
        $this->permissions = new Permissions('read');
    }

    /**
     * @test
     */
    public function it_has_a_read_permission()
    {
        $this->assertSame('read', $this->permissions->getRead());
    }
}

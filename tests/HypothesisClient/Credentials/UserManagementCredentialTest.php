<?php

namespace tests\eLife\HypothesisClient\Credentials;

use eLife\HypothesisClient\Credentials\UserManagementCredential;
use PHPUnit_Framework_TestCase;
use Serializable;

/**
 * @covers \eLife\HypothesisClient\Credentials\UserManagementCredential
 */
class UserManagementCredentialTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_getters()
    {
        $credential = new UserManagementCredential('foo', 'baz');
        $this->assertEquals('foo', $credential->getClientId());
        $this->assertEquals('baz', $credential->getSecretKey());
    }

    /**
     * @test
     */
    public function it_can_be_serialized()
    {
        $credential = new UserManagementCredential('foo', 'baz');
        $this->assertInstanceOf(Serializable::class, $credential);
        $this->assertEquals($credential, unserialize(serialize($credential)));
    }
}

<?php

namespace tests\eLife\HypothesisClient\Credentials;

use eLife\HypothesisClient\Credentials\Credentials;
use eLife\HypothesisClient\Credentials\JWTSigningCredential;
use eLife\HypothesisClient\Credentials\UserManagementCredential;
use PHPUnit_Framework_TestCase;

/**
 * @covers \eLife\HypothesisClient\Credentials\Credentials
 */
class CredentialsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_getters()
    {
        $credentials = new Credentials(new UserManagementCredential('foo', 'baz'), new JWTSigningCredential('foo', 'baz'), 'authority', 'group');
        $this->assertEquals(new UserManagementCredential('foo', 'baz'), $credentials->userManagement());
        $this->assertEquals(new JWTSigningCredential('foo', 'baz'), $credentials->jwtSigning());
        $this->assertEquals('authority', $credentials->getAuthority());
    }
}

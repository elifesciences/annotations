<?php

namespace tests\eLife\HypothesisClient\Credentials;

use eLife\HypothesisClient\Credentials\UserManagementCredentials;
use PHPUnit\Framework\TestCase;

/**
 * @covers \eLife\HypothesisClient\Credentials\UserManagementCredentials
 */
final class UserManagementCredentialsTest extends TestCase
{
    /**
     * @test
     */
    public function it_will_return_basic_authorization_string()
    {
        $credentials = new UserManagementCredentials('foo', 'baz', 'authority');
        $this->assertSame(sprintf('Basic %s', base64_encode('foo:baz')), $credentials->getAuthorizationBasic());
    }
}

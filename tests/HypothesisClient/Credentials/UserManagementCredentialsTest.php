<?php

namespace tests\eLife\HypothesisClient\Credentials;

use eLife\HypothesisClient\Credentials\UserManagementCredentials;
use PHPUnit_Framework_TestCase;

/**
 * @covers \eLife\HypothesisClient\Credentials\UserManagementCredentials
 */
class UserManagementCredentialsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_will_return_basic_authorization_string()
    {
        $credentials = new UserManagementCredentials('foo', 'baz', 'authority');
        $this->assertEquals(sprintf('Basic %s', base64_encode('foo:baz')), $credentials->getAuthorizationBasic());
    }
}

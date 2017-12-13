<?php

namespace tests\eLife\HypothesisClient\Credentials;

use eLife\HypothesisClient\Credentials\JWTSigningCredentials;
use Firebase\JWT\JWT;
use PHPUnit_Framework_TestCase;
use Serializable;

/**
 * @covers \eLife\HypothesisClient\Credentials\JWTSigningCredentials
 */
class JWTSigningCredentialsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_be_serialized()
    {
        $credentials = new JWTSigningCredentials('foo', 'baz', 'authority', 300);
        $this->assertInstanceOf(Serializable::class, $credentials);
        $this->assertEquals($credentials, unserialize(serialize($credentials)));
    }

    /**
     * @test
     */
    public function it_may_have_an_expire_time()
    {
        $credentials = new JWTSigningCredentials('foo', 'baz', 'authority');
        $this->assertGreaterThan(0, $credentials->getExpireTime() - $credentials->getStartTime());
        $credentials = new JWTSigningCredentials('foo', 'baz', 'authority', 100);
        $this->assertEquals(100, $credentials->getExpireTime() - $credentials->getStartTime());
    }

    /**
     * @test
     */
    public function it_may_have_a_start_time()
    {
        $credentials = new JWTSigningCredentials('foo', 'baz', 'authority');
        $this->assertGreaterThanOrEqual(time(), $credentials->getStartTime());
        $start_time = time();
        $credentials = new JWTSigningCredentials('foo', 'baz', 'authority', 100, $start_time);
        $this->assertEquals($start_time, $credentials->getStartTime());
    }

    /**
     * @test
     */
    public function it_can_generate_a_jwt_token()
    {
        $credentials = new JWTSigningCredentials('foo', 'baz', 'authority', 300, $now = time());
        $payload = [
            'aud' => 'hypothes.is',
            'iss' => 'foo',
            'sub' => 'acct:username@authority',
            'nbf' => $now,
            'exp' => $now + 300,
        ];
        $this->assertEquals(JWT::encode($payload, 'baz', 'HS256'), $credentials->getJWT('username'));
    }
}

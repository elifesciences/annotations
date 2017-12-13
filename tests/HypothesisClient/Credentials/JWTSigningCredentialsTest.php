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
        $this->assertGreaterThan(0, $credentials->getExpire());
        $credentials = new JWTSigningCredentials('foo', 'baz', 'authority', 100);
        $this->assertEquals(100, $credentials->getExpire());
    }

    /**
     * @test
     */
    public function it_can_generate_a_jwt_token()
    {
        $credentials = new JWTSigningCredentials('foo', 'baz', 'authority', 300);
        $now = $_SERVER['REQUEST_TIME'];
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

<?php

namespace tests\eLife\HypothesisClient\Credentials;

use eLife\HypothesisClient\Credentials\JWTSigningCredential;
use PHPUnit_Framework_TestCase;
use Serializable;

/**
 * @covers \eLife\HypothesisClient\Credentials\JWTSigningCredential
 */
class JWTSigningCredentialTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_getters()
    {
        $credential = new JWTSigningCredential('foo', 'baz');
        $this->assertEquals('foo', $credential->getClientId());
        $this->assertEquals('baz', $credential->getSecretKey());
    }

    /**
     * @test
     */
    public function it_can_be_serialized()
    {
        $credential = new JWTSigningCredential('foo', 'baz', 300);
        $this->assertInstanceOf(Serializable::class, $credential);
        $this->assertEquals($credential, unserialize(serialize($credential)));
    }

    /**
     * @test
     */
    public function it_may_have_an_expire_time()
    {
        $credential = new JWTSigningCredential('foo', 'baz');
        $this->assertGreaterThan(0, $credential->getExpire());
        $credential = new JWTSigningCredential('foo', 'baz', 100);
        $this->assertEquals(100, $credential->getExpire());
    }
}

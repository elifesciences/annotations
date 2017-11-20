<?php

namespace tests\eLife\HypothesisClient\Credentials;

use eLife\HypothesisClient\Credentials\Credentials;
use PHPUnit_Framework_TestCase;
use Serializable;

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
        $credentials = new Credentials('foo', 'baz', 'authority');
        $this->assertEquals('foo', $credentials->getClientId());
        $this->assertEquals('baz', $credentials->getSecretKey());
        $this->assertEquals('authority', $credentials->getAuthority());
        $this->assertEquals([
            'clientId' => 'foo',
            'secret' => 'baz',
            'authority' => 'authority',
        ], $credentials->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_serialized()
    {
        $credentials = new Credentials('foo', 'baz', 'authority');
        $this->assertInstanceOf(Serializable::class, $credentials);
        $this->assertEquals($credentials, unserialize(serialize($credentials)));
    }
}

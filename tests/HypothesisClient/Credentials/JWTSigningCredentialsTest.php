<?php

namespace tests\eLife\HypothesisClient\Credentials;

use eLife\HypothesisClient\Clock\Clock;
use eLife\HypothesisClient\Credentials\JWTSigningCredentials;
use Firebase\JWT\JWT;
use PHPUnit_Framework_TestCase;

/**
 * @covers \eLife\HypothesisClient\Credentials\JWTSigningCredentials
 */
class JWTSigningCredentialsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_generate_a_jwt_token()
    {
        $now = 1500000000;
        $clock = $this->createMock(Clock::class);
        $clock
            ->expects($this->once())
            ->method('time')
            ->willReturn($now);
        $credentials = new JWTSigningCredentials('foo', 'baz', 'authority', $clock, 300);

        $generatedToken = $credentials->getJWT('username');

        JWT::$timestamp = $now;
        $this->assertEquals(
            [
                'aud' => 'hypothes.is',
                'iss' => 'foo',
                'sub' => 'acct:username@authority',
                'nbf' => $now,
                'exp' => $now + 300,
            ],
            (array) JWT::decode($generatedToken, 'baz', ['HS256'])
        );
    }
}

<?php

namespace tests\eLife\HypothesisClient\Credentials;

use eLife\HypothesisClient\Clock\FixedClock;
use eLife\HypothesisClient\Credentials\JWTSigningCredentials;
use Firebase\JWT\JWT;
use PHPUnit_Framework_TestCase;

/**
 * @covers \eLife\HypothesisClient\Credentials\JWTSigningCredentials
 */
final class JWTSigningCredentialsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_generate_a_jwt_token()
    {
        $credentials = new JWTSigningCredentials($clientId = 'foo', $clientSecret = 'baz', $authority = 'authority', $clock = new FixedClock(), $expire = 300);

        $generatedToken = $credentials->getJWT($username = 'username');

        $start = $clock->time();
        $this->assertEquals(
            [
                'aud' => 'hypothes.is',
                'iss' => $clientId,
                'sub' => "acct:{$username}@{$authority}",
                'nbf' => $start,
                'exp' => $start + $expire,
            ],
            (array) JWT::decode($generatedToken, $clientSecret, ['HS256'])
        );
    }
}

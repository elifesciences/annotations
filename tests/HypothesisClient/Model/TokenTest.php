<?php

namespace tests\eLife\HypothesisClient\Model;

use eLife\HypothesisClient\Model\Model;
use eLife\HypothesisClient\Model\Token;
use eLife\HypothesisClient\Model\User;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;

/**
 * @covers \eLife\HypothesisClient\Model\Token
 */
final class TokenTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_an_access_token()
    {
        $token = new Token('access_token', 'token_type', 1000, 'refresh_token');

        $this->assertEquals('access_token', $token->getAccessToken());
    }

    /**
     * @test
     */
    public function it_has_a_token_type()
    {
        $token = new Token('access_token', 'token_type', 1000, 'refresh_token');

        $this->assertEquals('token_type', $token->getTokenType());
    }

    /**
     * @test
     */
    public function it_has_an_expires_in_value()
    {
        $token = new Token('access_token', 'token_type', 1000, 'refresh_token');

        $this->assertEquals(1000, $token->getExpiresIn());
    }

    /**
     * @test
     */
    public function it_has_a_refresh_token()
    {
        $token = new Token('access_token', 'token_type', 1000, 'refresh_token');

        $this->assertEquals('refresh_token', $token->getRefreshToken());
    }
}

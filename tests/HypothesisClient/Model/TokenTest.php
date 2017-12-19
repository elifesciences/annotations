<?php

namespace tests\eLife\HypothesisClient\Model;

use eLife\HypothesisClient\Model\Token;
use PHPUnit_Framework_TestCase;

/**
 * @covers \eLife\HypothesisClient\Model\Token
 */
final class TokenTest extends PHPUnit_Framework_TestCase
{
    /** @var Token */
    private $token;

    /**
     * @before
     */
    public function prepare_token()
    {
        $this->token = new Token('access_token', 'token_type', 1000, 'refresh_token');
    }

    /**
     * @test
     */
    public function it_has_an_access_token()
    {
        $this->assertEquals('access_token', $this->token->getAccessToken());
    }

    /**
     * @test
     */
    public function it_has_a_token_type()
    {
        $this->assertEquals('token_type', $this->token->getTokenType());
    }

    /**
     * @test
     */
    public function it_has_an_expires_in_value()
    {
        $this->assertEquals(1000, $this->token->getExpiresIn());
    }

    /**
     * @test
     */
    public function it_has_a_refresh_token()
    {
        $this->assertEquals('refresh_token', $this->token->getRefreshToken());
    }
}

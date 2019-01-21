<?php

namespace tests\eLife\HypothesisClient\Model;

use eLife\HypothesisClient\Model\Token;
use PHPUnit\Framework\TestCase;

/**
 * @covers \eLife\HypothesisClient\Model\Token
 */
final class TokenTest extends TestCase
{
    /** @var Token */
    private $token;

    /**
     * @before
     */
    public function prepare_token()
    {
        $this->token = new Token('access_token', 'token_type', 1000.99, 'refresh_token');
    }

    /**
     * @test
     */
    public function it_has_an_access_token()
    {
        $this->assertSame('access_token', $this->token->getAccessToken());
    }

    /**
     * @test
     */
    public function it_has_a_token_type()
    {
        $this->assertSame('token_type', $this->token->getTokenType());
    }

    /**
     * @test
     */
    public function it_has_an_expires_in_value()
    {
        $this->assertSame(1000.99, $this->token->getExpiresIn());
    }

    /**
     * @test
     */
    public function it_has_a_refresh_token()
    {
        $this->assertSame('refresh_token', $this->token->getRefreshToken());
    }
}

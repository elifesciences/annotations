<?php

namespace eLife\HypothesisClient\Model;

final class Token
{
    private $accessToken;
    private $tokenType;
    private $expiresIn;
    private $refreshToken;

    /**
     * @internal
     */
    public function __construct(
        string $accessToken,
        string $tokenType,
        float $expiresIn,
        string $refreshToken
    ) {
        $this->accessToken = $accessToken;
        $this->tokenType = $tokenType;
        $this->expiresIn = $expiresIn;
        $this->refreshToken = $refreshToken;
    }

    public function getAccessToken() : string
    {
        return $this->accessToken;
    }

    public function getTokenType() : string
    {
        return $this->tokenType;
    }

    public function getExpiresIn() : float
    {
        return $this->expiresIn;
    }

    public function getRefreshToken() : string
    {
        return $this->refreshToken;
    }
}

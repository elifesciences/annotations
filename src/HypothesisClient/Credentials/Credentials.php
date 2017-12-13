<?php

namespace eLife\HypothesisClient\Credentials;

abstract class Credentials
{
    private $authority;
    private $clientId;
    private $clientSecret;

    public function __construct(string $clientId, string $clientSecret, string $authority)
    {
        $this->clientId = trim($clientId);
        $this->clientSecret = trim($clientSecret);
        $this->authority = trim($authority);
    }

    public function getClientId() : string
    {
        return $this->clientId;
    }

    public function getClientSecret() : string
    {
        return $this->clientSecret;
    }

    public function getAuthority() : string
    {
        return $this->authority;
    }
}

<?php

namespace eLife\HypothesisClient\Credentials;

use Serializable;

abstract class Credentials implements Serializable
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

    public function toArray() : array
    {
        return [
            'clientId' => $this->getClientId(),
            'clientSecret' => $this->getClientSecret(),
            'authority' => $this->getAuthority(),
        ];
    }

    public function serialize() : string
    {
        return json_encode($this->toArray());
    }

    public function unserialize($serialized)
    {
        $data = json_decode($serialized, true);

        $this->clientId = $data['clientId'];
        $this->clientSecret = $data['clientSecret'];
        $this->authority = $data['authority'];
    }
}

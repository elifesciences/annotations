<?php

namespace eLife\HypothesisClient\Credentials;

use Serializable;

abstract class Credential implements Serializable
{
    private $clientId;
    private $secret;

    public function __construct(string $clientId, string $secret)
    {
        $this->clientId = trim($clientId);
        $this->secret = trim($secret);
    }

    public function getClientId() : string
    {
        return $this->clientId;
    }

    public function getSecretKey() : string
    {
        return $this->secret;
    }

    public function toArray() : array
    {
        return [
            'clientId' => $this->getClientId(),
            'secret' => $this->getSecretKey(),
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
        $this->secret = $data['secret'];
    }
}

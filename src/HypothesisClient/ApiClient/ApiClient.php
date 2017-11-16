<?php

namespace eLife\HypothesisClient\ApiClient;

use eLife\HypothesisClient\Credentials\Credentials;

interface ApiClient
{
    public function getCredentials() : Credentials;
}

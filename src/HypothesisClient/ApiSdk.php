<?php

namespace eLife\HypothesisClient;

use eLife\HypothesisClient\ApiClient\UsersClient;
use eLife\HypothesisClient\Client\Users;
use eLife\HypothesisClient\Credentials\Credentials;
use eLife\HypothesisClient\HttpClient\HttpClient;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerAwareInterface;

final class ApiSdk
{
    /** @var Credentials */
    private $credentials;
    /** @var HttpClient */
    private $httpClient;
    /** @var SerializerAwareInterface */
    private $normalizer;
    /** @var Users */
    private $users;

    public function __construct(HttpClient $httpClient, Credentials $credentials = null)
    {
        $this->httpClient = $httpClient;
        $this->credentials = $credentials;
        $this->normalizer = new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter());
        $this->users = new Users(new UsersClient($this->httpClient, $this->credentials, []), $this->normalizer);
    }

    public function users() : Users
    {
        return $this->users;
    }
}

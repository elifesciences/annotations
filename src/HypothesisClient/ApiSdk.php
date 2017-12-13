<?php

namespace eLife\HypothesisClient;

use eLife\HypothesisClient\ApiClient\UsersClient;
use eLife\HypothesisClient\Client\Users;
use eLife\HypothesisClient\Credentials\UserManagementCredentials;
use eLife\HypothesisClient\HttpClient\HttpClient;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerAwareInterface;

final class ApiSdk
{
    /** @var UserManagementCredentials */
    private $userManagement;
    /** @var HttpClient */
    private $httpClient;
    /** @var SerializerAwareInterface */
    private $normalizer;
    /** @var Users */
    private $users;

    public function __construct(HttpClient $httpClient, UserManagementCredentials $userManagement = null)
    {
        $this->httpClient = $httpClient;
        $this->userManagement = $userManagement;
        $this->normalizer = new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter());
        $this->users = new Users(new UsersClient($this->httpClient, $this->userManagement, []), $this->normalizer);
    }

    public function users() : Users
    {
        return $this->users;
    }
}

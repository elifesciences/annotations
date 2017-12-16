<?php

namespace eLife\HypothesisClient;

use eLife\HypothesisClient\ApiClient\SearchClient;
use eLife\HypothesisClient\ApiClient\TokenClient;
use eLife\HypothesisClient\ApiClient\UsersClient;
use eLife\HypothesisClient\Client\Search;
use eLife\HypothesisClient\Client\Token;
use eLife\HypothesisClient\Client\Users;
use eLife\HypothesisClient\Credentials\JWTSigningCredentials;
use eLife\HypothesisClient\Credentials\UserManagementCredentials;
use eLife\HypothesisClient\HttpClient\HttpClient;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerAwareInterface;

final class ApiSdk
{
    /** @var string */
    private $group;
    /** @var HttpClient */
    private $httpClient;
    /** @var JWTSigningCredentials */
    private $jwtSigning;
    /** @var SerializerAwareInterface */
    private $normalizer;
    /** @var Search */
    private $search;
    /** @var Token */
    private $token;
    /** @var Users */
    private $users;
    /** @var UserManagementCredentials */
    private $userManagement;

    public function __construct(HttpClient $httpClient, UserManagementCredentials $userManagement = null, JWTSigningCredentials $jwtSigning, string $group = '__world__')
    {
        $this->httpClient = $httpClient;
        $this->userManagement = $userManagement;
        $this->jwtSigning = $jwtSigning;
        $this->group = $group;
        $this->normalizer = new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter());
        $this->search = new Search(new SearchClient($this->httpClient, $this->group, []), $this->normalizer);
        $this->token = new Token(new TokenClient($this->httpClient, $this->jwtSigning, []), $this->normalizer);
        $this->users = new Users(new UsersClient($this->httpClient, $this->userManagement, []), $this->normalizer);

    }

    public function users() : Users
    {
        return $this->users;
    }

    public function token() : Token
    {
        return $this->token;
    }

    public function search() : Search
    {
        return $this->search;
    }
}

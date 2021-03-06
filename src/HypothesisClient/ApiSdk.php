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
use eLife\HypothesisClient\Serializer\Annotation;
use eLife\HypothesisClient\Serializer\AnnotationDenormalizer;
use eLife\HypothesisClient\Serializer\TokenDenormalizer;
use eLife\HypothesisClient\Serializer\UserDenormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

final class ApiSdk
{
    private $group;
    private $httpClient;
    private $jwtSigning;
    private $search;
    private $serializer;
    private $token;
    private $users;
    private $userManagement;

    public function __construct(HttpClient $httpClient, UserManagementCredentials $userManagement = null, JWTSigningCredentials $jwtSigning, string $group = '__world__')
    {
        $this->httpClient = $httpClient;
        $this->userManagement = $userManagement;
        $this->jwtSigning = $jwtSigning;
        $this->group = $group;
        $this->serializer = new Serializer([
            new Annotation\DocumentDenormalizer(),
            new Annotation\TargetDenormalizer(),
            new Annotation\Target\SelectorDenormalizer(),
            new Annotation\Target\Selector\TextQuoteDenormalizer(),
            new Annotation\PermissionsDenormalizer(),
            new AnnotationDenormalizer(),
            new TokenDenormalizer(),
            new UserDenormalizer(),
        ], [new JsonEncoder()]);
        $this->search = new Search(new SearchClient($this->httpClient, $this->group, []), $this->serializer);
        $this->token = new Token(new TokenClient($this->httpClient, $this->jwtSigning, []), $this->serializer);
        $this->users = new Users(new UsersClient($this->httpClient, $this->userManagement, []), $this->serializer);
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

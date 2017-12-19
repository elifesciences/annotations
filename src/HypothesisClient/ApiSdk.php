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
use eLife\HypothesisClient\Serializer\AnnotationNormalizer;
use eLife\HypothesisClient\Serializer\TokenNormalizer;
use eLife\HypothesisClient\Serializer\UserNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

final class ApiSdk
{
    /** @var string */
    private $group;
    /** @var HttpClient */
    private $httpClient;
    /** @var JWTSigningCredentials */
    private $jwtSigning;
    /** @var Search */
    private $search;
    /** @var Serializer */
    private $serializer;
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
        $this->serializer = new Serializer([
            new Annotation\DocumentNormalizer(),
            new Annotation\TargetNormalizer(),
            new Annotation\Target\SelectorNormalizer(),
            new Annotation\Target\Selector\FragmentNormalizer(),
            new Annotation\Target\Selector\RangeNormalizer(),
            new Annotation\Target\Selector\TextPositionNormalizer(),
            new Annotation\Target\Selector\TextQuoteNormalizer(),
            new Annotation\PermissionsNormalizer(),
            new AnnotationNormalizer(),
            new TokenNormalizer(),
            new UserNormalizer(),
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

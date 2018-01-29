<?php

namespace tests\eLife\HypothesisClient\Client;

use eLife\HypothesisClient\ApiClient\TokenClient;
use eLife\HypothesisClient\Client\Token;
use eLife\HypothesisClient\Clock\SystemClock;
use eLife\HypothesisClient\Credentials\JWTSigningCredentials;
use eLife\HypothesisClient\HttpClient\HttpClient;
use eLife\HypothesisClient\Model\Token as ModelToken;
use eLife\HypothesisClient\Model\User;
use eLife\HypothesisClient\Result\ArrayResult;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PHPUnit_Framework_TestCase;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\Cache\Simple\TraceableCache;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use tests\eLife\HypothesisClient\RequestConstraint;

/**
 * @covers \eLife\HypothesisClient\Client\Token
 */
class TokenTest extends PHPUnit_Framework_TestCase
{
    private $authority;
    private $clientId;
    private $clientSecret;
    private $credentials;
    private $denormalizer;
    private $group;
    private $httpClient;
    /** @var Token */
    private $token;
    /** @var TokenClient */
    private $tokenClient;
    /** @var TraceableCache */
    private $cache;

    /**
     * @before
     */
    public function prepareDependencies()
    {
        $this->clientId = 'client_id';
        $this->clientSecret = 'client_secret';
        $this->authority = 'authority';
        $this->group = 'group';
        $this->credentials = $this->getMockBuilder(JWTSigningCredentials::class)
            ->setConstructorArgs([$this->clientId, $this->clientSecret, $this->authority, new SystemClock()])
            ->getMock();
        $this->denormalizer = $this->getMockBuilder(DenormalizerInterface::class)
            ->setMethods(['denormalize', 'supportsDenormalization'])
            ->getMock();
        $this->httpClient = $this->getMockBuilder(HttpClient::class)
            ->setMethods(['send'])
            ->getMock();
        $this->tokenClient = new TokenClient($this->httpClient, $this->credentials);
        $this->token = new Token($this->tokenClient, $this->denormalizer);
        $this->cache = new TraceableCache(new ArrayCache());
    }

    /**
     * @test
     */
    public function it_will_get_a_token()
    {
        $this->credentials
            ->method('getJWT')
            ->with('username')
            ->willReturn('jwt');
        $request = new Request(
            'POST',
            'token',
            ['User-Agent' => 'HypothesisClient'],
            http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => 'jwt',
            ])
        );
        $response = new FulfilledPromise(new ArrayResult([
            'access_token' => 'access_token',
            'token_type' => 'Bearer',
            'expires_in' => (float) 3600,
            'refresh_token' => 'refresh_token',
        ]));
        $token = new ModelToken('access_token', 'Bearer', 3600, 'refresh_token');
        $this->denormalizer
            ->method('denormalize')
            ->with($response->wait()->toArray(), ModelToken::class)
            ->willReturn($token);
        $this->httpClient
            ->expects($this->once())
            ->method('send')
            ->with(RequestConstraint::equalTo($request))
            ->willReturn($response);
        $this->assertEquals($token, $this->token->get('username')->wait());
    }

    /**
     * @test
     */
    public function it_caches_tokens()
    {
        $tokens = new Token($this->tokenClient, $this->denormalizer, $this->cache);

        $this->credentials
            ->method('getJWT')
            ->with('username')
            ->willReturn('jwt');
        $request = new Request(
            'POST',
            'token',
            ['User-Agent' => 'HypothesisClient'],
            http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => 'jwt',
            ])
        );
        $response = new FulfilledPromise(new ArrayResult([
            'access_token' => 'access_token',
            'token_type' => 'Bearer',
            'expires_in' => (float) 3600,
            'refresh_token' => 'refresh_token',
        ]));
        $expected = new ModelToken('access_token', 'Bearer', 3600, 'refresh_token');
        $this->denormalizer
            ->method('denormalize')
            ->with($response->wait()->toArray(), ModelToken::class)
            ->willReturn($expected);
        $this->httpClient
            ->expects($this->once())
            ->method('send')
            ->with(RequestConstraint::equalTo($request))
            ->willReturn($response);

        $this->assertEquals($expected, $tokens->get('username')->wait());

        $cacheCalls = $this->cache->getCalls();
        $this->assertCount(2, $cacheCalls);

        $this->assertEquals($expected, $tokens->get('username')->wait());

        $cacheCalls = $this->cache->getCalls();
        $this->assertCount(1, $cacheCalls);
    }

    /**
     * @test
     */
    public function it_ignores_broken_cached_tokens()
    {
        $cache = $this->getMockBuilder(CacheInterface::class)
            ->getMock();

        $tokens = new Token($this->tokenClient, $this->denormalizer, $cache);

        $this->credentials
            ->method('getJWT')
            ->with('username')
            ->willReturn('jwt');
        $request = new Request(
            'POST',
            'token',
            ['User-Agent' => 'HypothesisClient'],
            http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => 'jwt',
            ])
        );
        $response = new FulfilledPromise(new ArrayResult([
            'access_token' => 'access_token',
            'token_type' => 'Bearer',
            'expires_in' => (float) 3600,
            'refresh_token' => 'refresh_token',
        ]));
        $expected = new ModelToken('access_token', 'Bearer', 3600, 'refresh_token');
        $this->denormalizer
            ->method('denormalize')
            ->with($response->wait()->toArray(), ModelToken::class)
            ->willReturn($expected);
        $this->httpClient
            ->expects($this->once())
            ->method('send')
            ->with(RequestConstraint::equalTo($request))
            ->willReturn($response);
        $cache
            ->expects($this->once())
            ->method('get')
            ->with('hypothesis.token.username')
            ->willReturn('foo');
        $cache
            ->expects($this->once())
            ->method('set')
            ->with('hypothesis.token.username', $expected, 3600);

        $this->assertEquals($expected, $tokens->get('username')->wait());
    }
}

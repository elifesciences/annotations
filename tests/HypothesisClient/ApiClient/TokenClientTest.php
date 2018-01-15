<?php

namespace tests\eLife\HypothesisClient\HttpClient;

use eLife\HypothesisClient\ApiClient\TokenClient;
use eLife\HypothesisClient\Clock\Clock;
use eLife\HypothesisClient\Clock\SystemClock;
use eLife\HypothesisClient\Credentials\JWTSigningCredentials;
use eLife\HypothesisClient\HttpClient\HttpClient;
use eLife\HypothesisClient\Result\ArrayResult;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PHPUnit_Framework_TestCase;
use tests\eLife\HypothesisClient\RequestConstraint;

/**
 * @covers \eLife\HypothesisClient\ApiClient\TokenClient
 */
final class TokenClientTest extends PHPUnit_Framework_TestCase
{
    private $clock;
    private $credentials;
    private $httpClient;
    /** @var TokenClient */
    private $tokenClient;

    /**
     * @before
     */
    protected function setUpClient()
    {
        $this->credentials = $this->getMockBuilder(JWTSigningCredentials::class)
            ->setConstructorArgs(['client_id', 'secret_key', 'authority', new SystemClock(), 600])
            ->getMock();
        $this->httpClient = $this->createMock(HttpClient::class);
        $this->tokenClient = new TokenClient(
            $this->httpClient,
            $this->credentials,
            ['X-Foo' => 'bar']
        );
    }

    /**
     * @test
     */
    public function it_can_claim_a_token()
    {
        $this->credentials
            ->method('getJWT')
            ->with('username')
            ->willReturn('jwt');
        $request = new Request(
            'POST',
            'token',
            ['X-Foo' => 'bar', 'User-Agent' => 'HypothesisClient'],
            http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => 'jwt',
            ])
        );
        $response = new FulfilledPromise(new ArrayResult(['foo' => ['bar', 'baz']]));
        $this->httpClient
            ->expects($this->once())
            ->method('send')
            ->with(RequestConstraint::equalTo($request))
            ->willReturn($response);
        $this->assertEquals($response, $this->tokenClient->getToken([], 'username'));
    }
}

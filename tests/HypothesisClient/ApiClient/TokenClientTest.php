<?php

namespace tests\eLife\HypothesisClient\HttpClient;

use eLife\HypothesisClient\ApiClient\TokenClient;
use eLife\HypothesisClient\Clock\FixedClock;
use eLife\HypothesisClient\Credentials\JWTSigningCredentials;
use eLife\HypothesisClient\HttpClient\HttpClient;
use eLife\HypothesisClient\Result\ArrayResult;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use tests\eLife\HypothesisClient\RequestConstraint;

/**
 * @covers \eLife\HypothesisClient\ApiClient\TokenClient
 */
final class TokenClientTest extends TestCase
{
    /** @var JWTSigningCredentials */
    private $credentials;
    private $httpClient;
    /** @var TokenClient */
    private $tokenClient;

    /**
     * @before
     */
    protected function setUpClient()
    {
        $this->credentials = new JWTSigningCredentials('client_id', 'secret_key', 'authority', new FixedClock(), 600);
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
        $request = new Request(
            'POST',
            'token',
            ['X-Foo' => 'bar', 'User-Agent' => 'HypothesisClient'],
            http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $this->credentials->getJWT('username'),
            ])
        );
        $response = new FulfilledPromise(new ArrayResult(['foo' => ['bar', 'baz']]));
        $this->httpClient
            ->expects($this->once())
            ->method('send')
            ->with(RequestConstraint::equalTo($request))
            ->willReturn($response);
        $this->assertSame($response, $this->tokenClient->getToken([], 'username'));
    }
}

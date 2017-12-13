<?php

namespace tests\eLife\HypothesisClient\HttpClient;

use eLife\HypothesisClient\ApiClient\UsersClient;
use eLife\HypothesisClient\Credentials\UserManagementCredentials;
use eLife\HypothesisClient\HttpClient\HttpClient;
use eLife\HypothesisClient\Result\ArrayResult;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PHPUnit_Framework_TestCase;
use tests\eLife\HypothesisClient\RequestConstraint;
use Throwable;
use TypeError;

/**
 * @covers \eLife\HypothesisClient\ApiClient\UsersClient
 */
final class UsersClientTest extends PHPUnit_Framework_TestCase
{
    private $credentials;
    private $httpClient;
    /** @var UsersClient */
    private $usersClient;
    /** @var UsersClient */
    private $usersClientAnonymous;

    /**
     * @before
     */
    protected function setUpClient()
    {
        $this->credentials = $this->getMockBuilder(UserManagementCredentials::class)
            ->setConstructorArgs(['client_id', 'secret_key', 'authority'])
            ->getMock();
        $this->credentials
            ->method('getAuthorizationBasic')
            ->willReturn('Basic '.base64_encode('client_id:secret_key'));
        $this->credentials
            ->method('getAuthority')
            ->willReturn('authority');
        $this->httpClient = $this->createMock(HttpClient::class);
        $this->usersClient = new UsersClient(
            $this->httpClient,
            $this->credentials,
            ['X-Foo' => 'bar']
        );
        $this->usersClientAnonymous = new UsersClient(
            $this->httpClient,
            null,
            ['X-Foo' => 'bar']
        );
    }

    /**
     * @test
     */
    public function it_requires_a_http_client()
    {
        try {
            new UsersClient('foo');
            $this->fail('A HttpClient is required');
        } catch (TypeError $error) {
            $this->assertTrue(true, 'A HttpClient is required');
            $this->assertContains(
                'must implement interface '.HttpClient::class.', string given',
                $error->getMessage()
            );
        }
    }

    /**
     * @test
     */
    public function it_gets_a_user()
    {
        $request = new Request(
            'PATCH',
            'users/user',
            ['X-Foo' => 'bar', 'User-Agent' => 'HypothesisClient'],
            '{}'
        );
        $response = new FulfilledPromise(
            new ArrayResult(['foo' => ['bar', 'baz']])
        );
        $this->httpClient
            ->expects($this->once())
            ->method('send')
            ->with(RequestConstraint::equalTo($request))
            ->willReturn($response);
        $this->assertSame($response, $this->usersClientAnonymous->getUser([], 'user'));
    }

    /**
     * @test
     */
    public function it_creates_a_user_with_credentials()
    {
        // Some operations require credentials, react if they are missing.
        try {
            $this->usersClientAnonymous->createUser([], 'userid', 'email@email.com', 'display_name');
            $this->fail('Credentials are required, if requested');
        } catch (Throwable $error) {
            $this->assertTrue(true, 'Credentials are required, if requested');
            $this->assertContains('Call to a member function getAuthority() on null', $error->getMessage());
        }
        $request = new Request(
            'POST',
            'users',
            ['X-Foo' => 'bar', 'Authorization' => 'Basic '.base64_encode('client_id:secret_key'), 'User-Agent' => 'HypothesisClient'],
            json_encode(['authority' => 'authority', 'username' => 'userid', 'email' => 'email@email.com', 'display_name' => 'display_name'])
        );
        $response = new FulfilledPromise(new ArrayResult(['foo' => ['bar', 'baz']]));
        $this->httpClient
            ->expects($this->once())
            ->method('send')
            ->with(RequestConstraint::equalTo($request))
            ->willReturn($response);
        $this->assertEquals($response, $this->usersClient->createUser([], 'userid', 'email@email.com', 'display_name'));
    }

    /**
     * @test
     */
    public function it_modifies_a_user()
    {
        $request = new Request(
            'PATCH',
            'users/userid',
            ['X-Foo' => 'bar', 'Authorization' => 'Basic '.base64_encode('client_id:secret_key'), 'User-Agent' => 'HypothesisClient'],
            json_encode(['email' => 'email@email.com'])
        );
        $response = new FulfilledPromise(new ArrayResult(['foo' => ['bar', 'baz']]));
        $this->httpClient
            ->expects($this->once())
            ->method('send')
            ->with(RequestConstraint::equalTo($request))
            ->willReturn($response);
        $this->assertEquals($response, $this->usersClient->updateUser([], 'userid', 'email@email.com'));
    }

    /**
     * @test
     */
    public function it_may_have_credentials()
    {
        $request = new Request(
            'PATCH',
            'users/user',
            ['X-Foo' => 'bar', 'Authorization' => 'Basic '.base64_encode('client_id:secret_key'), 'User-Agent' => 'HypothesisClient'],
            '{}'
        );
        $response = new FulfilledPromise(new ArrayResult(['foo' => ['bar', 'baz']]));
        $this->httpClient
            ->expects($this->once())
            ->method('send')
            ->with(RequestConstraint::equalTo($request))
            ->willReturn($response);
        $this->assertEquals($response, $this->usersClient->getUser([], 'user'));
    }
}

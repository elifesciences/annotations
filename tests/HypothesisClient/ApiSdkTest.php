<?php

namespace tests\eLife\HypothesisClient;

use eLife\HypothesisClient\ApiSdk;
use eLife\HypothesisClient\Client\Users;
use eLife\HypothesisClient\Credentials\Credentials;
use eLife\HypothesisClient\Credentials\JWTSigningCredential;
use eLife\HypothesisClient\Credentials\UserManagementCredential;
use eLife\HypothesisClient\HttpClient\HttpClient;
use eLife\HypothesisClient\Model\User;
use eLife\HypothesisClient\Result\ArrayResult;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PHPUnit_Framework_TestCase;

/**
 * @covers \eLife\HypothesisClient\ApiSdk
 */
final class ApiSdkTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_creates_a_users_client()
    {
        $this->assertInstanceOf(
            Users::class,
            (new ApiSdk($this->getMockBuilder(HttpClient::class)->getMock()))->users()
        );
    }

    /**
     * @test
     */
    public function it_may_have_credentials()
    {
        $httpClient = $this->getMockBuilder(HttpClient::class)
            ->getMock();
        $credentials = $this->getMockBuilder(Credentials::class)
            ->setConstructorArgs([new UserManagementCredential('client_id', 'secret_key'), new JWTSigningCredential('client_id', 'secret_key'), 'authority', 'group'])
            ->getMock();

        $credentials->expects($this->atLeastOnce())->method('getAuthorizationBasic')->willReturn('Basic '.base64_encode('client_id:secret_key'));

        $sdk = (new ApiSdk(
            $httpClient,
            $credentials
        ));

        $request = new Request(
            'PATCH',
            'users/username',
            ['Authorization' => 'Basic '.base64_encode('client_id:secret_key'), 'User-Agent' => 'HypothesisClient'],
            '{}'
        );
        $response = new FulfilledPromise(new ArrayResult([
            'username' => 'username',
            'email' => 'email@email.com',
            'display_name' => 'Display Name',
            'authority' => 'authority',
        ]));

        $user = new User('username', 'email@email.com', 'Display Name');
        $httpClient
            ->expects($this->once())
            ->method('send')
            ->with(RequestConstraint::equalTo($request))
            ->willReturn($response);
        $this->assertEquals($user, $sdk->users()->get('username')->wait());
    }
}

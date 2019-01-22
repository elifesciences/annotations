<?php

namespace tests\eLife\HypothesisClient;

use eLife\HypothesisClient\ApiSdk;
use eLife\HypothesisClient\Client\Users;
use eLife\HypothesisClient\Clock\SystemClock;
use eLife\HypothesisClient\Credentials\JWTSigningCredentials;
use eLife\HypothesisClient\Credentials\UserManagementCredentials;
use eLife\HypothesisClient\HttpClient\HttpClient;
use eLife\HypothesisClient\Model\User;
use eLife\HypothesisClient\Result\ArrayResult;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;

/**
 * @covers \eLife\HypothesisClient\ApiSdk
 */
final class ApiSdkTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_a_users_client()
    {
        $this->assertInstanceOf(
            Users::class,
            (new ApiSdk($this->getMockBuilder(HttpClient::class)->getMock(), null, new JWTSigningCredentials('client_it', 'client_secret', 'authority', new SystemClock())))->users()
        );
    }

    /**
     * @test
     */
    public function it_may_have_user_management_credentials()
    {
        $httpClient = $this->getMockBuilder(HttpClient::class)
            ->getMock();
        $userManagement = new UserManagementCredentials('client_id', 'secret_key', 'authority');

        $sdk = (new ApiSdk(
            $httpClient,
            $userManagement,
            new JWTSigningCredentials('client_it', 'client_secret', 'authority', new SystemClock())
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

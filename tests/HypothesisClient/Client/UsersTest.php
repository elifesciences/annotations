<?php

namespace tests\eLife\HypothesisClient\Client;

use eLife\HypothesisClient\ApiClient\UsersClient;
use eLife\HypothesisClient\Client\Users;
use eLife\HypothesisClient\Credentials\UserManagementCredentials;
use eLife\HypothesisClient\Exception\BadResponse;
use eLife\HypothesisClient\HttpClient\HttpClient;
use eLife\HypothesisClient\Model\User;
use eLife\HypothesisClient\Result\ArrayResult;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use tests\eLife\HypothesisClient\RequestConstraint;
use Traversable;

/**
 * @covers \eLife\HypothesisClient\Client\Users
 */
final class UsersTest extends TestCase
{
    private $authority;
    private $authorization;
    private $clientId;
    private $clientSecret;
    private $credentials;
    private $denormalizer;
    private $group;
    private $httpClient;
    /** @var Users */
    private $users;
    /** @var Users */
    private $usersAnonymous;
    /** @var UsersClient */
    private $usersClient;
    /** @var UsersClient */
    private $usersClientAnonymous;

    /**
     * @before
     */
    public function prepareDependencies()
    {
        $this->clientId = 'client_id';
        $this->clientSecret = 'client_secret';
        $this->authority = 'authority';
        $this->group = 'group';
        $this->authorization = sprintf('Basic %s', base64_encode($this->clientId.':'.$this->clientSecret));
        $this->credentials = new UserManagementCredentials($this->clientId, $this->clientSecret, $this->authority);
        $this->denormalizer = $this->getMockBuilder(DenormalizerInterface::class)
            ->setMethods(['denormalize', 'supportsDenormalization'])
            ->getMock();
        $this->httpClient = $this->getMockBuilder(HttpClient::class)
            ->setMethods(['send'])
            ->getMock();
        $this->usersClientAnonymous = new UsersClient($this->httpClient);
        $this->usersClient = new UsersClient($this->httpClient, $this->credentials);
        $this->usersAnonymous = new Users($this->usersClientAnonymous, $this->denormalizer);
        $this->users = new Users($this->usersClient, $this->denormalizer);
    }

    /**
     * @test
     */
    public function it_will_get_a_user()
    {
        $request = new Request(
            'PATCH',
            'users/username',
            ['User-Agent' => 'HypothesisClient'],
            '{}'
        );
        $response = new FulfilledPromise(new ArrayResult([
            'username' => 'username',
            'email' => 'email@email.com',
            'display_name' => 'Display Name',
            'authority' => 'authority',
        ]));
        $user = new User('username', 'email@email.com', 'Display Name');
        $this->denormalizer
            ->method('denormalize')
            ->with($response->wait()->toArray(), User::class)
            ->willReturn($user);
        $this->httpClient
            ->expects($this->once())
            ->method('send')
            ->with(RequestConstraint::equalTo($request))
            ->willReturn($response);
        $this->assertSame($user, $this->usersAnonymous->get('username')->wait());
    }

    /**
     * @test
     */
    public function it_will_create_a_user()
    {
        $data = [
            'authority' => 'authority',
            'username' => 'username',
            'email' => 'email@email.com',
            'display_name' => 'Display Name',
        ];
        $response_data = $data + ['userid' => sprintf('%s@%s', $data['username'], $data['authority'])];
        $request = new Request(
            'POST',
            'users',
            ['Authorization' => $this->authorization, 'User-Agent' => 'HypothesisClient'],
            json_encode($data)
        );
        $response = new FulfilledPromise(new ArrayResult($response_data));
        $user = new User('username', 'email@email.com', 'Display Name');
        $this->usersClient;
        $expectedUser = new User('username', 'email@email.com', 'Display Name', true);
        $this->denormalizer
            ->method('denormalize')
            ->with($response->wait()->toArray() + ['new' => true], User::class)
            ->willReturn($expectedUser);
        $this->httpClient
            ->expects($this->once())
            ->method('send')
            ->with(RequestConstraint::equalTo($request))
            ->willReturn($response);
        $createdUser = $this->users->create($user)->wait();
        $this->assertTrue($createdUser->isNew());
        $this->assertSame($expectedUser, $createdUser);
    }

    /**
     * @test
     */
    public function it_will_update_a_user()
    {
        $data = [
            'email' => 'email@email.com',
            'display_name' => 'Display Name',
        ];
        $response_data = $data + ['username' => 'username', 'authority' => 'authority', 'userid' => sprintf('%s@%s', 'username', 'authority')];
        $request = new Request(
            'PATCH',
            'users/username',
            ['Authorization' => $this->authorization, 'User-Agent' => 'HypothesisClient'],
            json_encode($data)
        );
        $response = new FulfilledPromise(new ArrayResult($response_data));
        $user = new User('username', 'email@email.com', 'Display Name');
        $this->usersClient;
        $this->denormalizer
            ->method('denormalize')
            ->with($response->wait()->toArray(), User::class)
            ->willReturn($user);
        $expectedUser = clone $user;
        $this->httpClient
            ->expects($this->once())
            ->method('send')
            ->with(RequestConstraint::equalTo($request))
            ->willReturn($response);
        $updatedUser = $this->users->update($user)->wait();
        $this->assertFalse($updatedUser->isNew());
        $this->assertEquals($expectedUser, $updatedUser);
    }

    /**
     * @test
     */
    public function it_will_upsert_a_new_user()
    {
        $data = [
            'authority' => 'authority',
            'username' => 'username',
            'email' => 'email@email.com',
            'display_name' => 'Display Name',
        ];
        $response_data = $data + ['userid' => sprintf('%s@%s', $data['username'], $data['authority'])];
        $request = new Request(
            'POST',
            'users',
            ['Authorization' => $this->authorization, 'User-Agent' => 'HypothesisClient'],
            json_encode($data)
        );
        $response = new FulfilledPromise(new ArrayResult($response_data));
        $user = new User('username', 'email@email.com', 'Display Name');
        $this->usersClient;
        $expectedUser = new User('username', 'email@email.com', 'Display Name', true);
        $this->denormalizer
            ->method('denormalize')
            ->with($response->wait()->toArray() + ['new' => true], User::class)
            ->willReturn($expectedUser);
        $this->httpClient
            ->expects($this->once())
            ->method('send')
            ->with(RequestConstraint::equalTo($request))
            ->willReturn($response);
        $upsertedUser = $this->users->upsert($user)->wait();
        $this->assertTrue($upsertedUser->isNew());
        $this->assertEquals($expectedUser, $upsertedUser);
    }

    /**
     * @test
     * @dataProvider existingUserProvider
     */
    public function it_will_upsert_an_existing_user(int $responseCode)
    {
        $post_data = [
            'authority' => 'authority',
            'username' => 'username',
            'email' => 'email@email.com',
            'display_name' => 'Display Name',
        ];
        $post_request = new Request(
            'POST',
            'users',
            ['Authorization' => $this->authorization, 'User-Agent' => 'HypothesisClient'],
            json_encode($post_data)
        );
        $post_response_mess = json_encode(['status' => 'failure', 'reason' => 'user with username username already exists']);
        $post_response = new Response($responseCode, [], $post_response_mess);
        $rejected_post_response = new RejectedPromise(new BadResponse($post_response_mess, $post_request, $post_response));
        $patch_data = [
            'email' => 'email@email.com',
            'display_name' => 'Display Name',
        ];
        $patch_request = new Request(
            'PATCH',
            'users/username',
            ['Authorization' => $this->authorization, 'User-Agent' => 'HypothesisClient'],
            json_encode($patch_data)
        );
        $patch_response_data = $post_data + ['userid' => sprintf('%s@%s', $post_data['username'], $post_data['authority'])];
        $patch_response = new FulfilledPromise(new ArrayResult($patch_response_data));
        $user = new User('username', 'email@email.com', 'Display Name');
        $this->usersClient;
        $this->httpClient
            ->expects($this->at(0))
            ->method('send')
            ->with(RequestConstraint::equalTo($post_request))
            ->willReturn($rejected_post_response);
        $this->denormalizer
            ->method('denormalize')
            ->with($patch_response->wait()->toArray(), User::class)
            ->willReturn($user);
        $expectedUser = clone $user;
        $this->httpClient
            ->expects($this->at(1))
            ->method('send')
            ->with(RequestConstraint::equalTo($patch_request))
            ->willReturn($patch_response);
        $upsertedUser = $this->users->upsert($user)->wait();
        $this->assertFalse($upsertedUser->isNew());
        $this->assertEquals($expectedUser, $upsertedUser);
    }

    public function existingUserProvider() : Traversable
    {
        yield '409 Conflict response' => [409];
        yield '400 Bad Request response' => [400];
    }

    /**
     * @test
     */
    public function it_will_fail_fast_on_errors_that_do_not_indicate_existing_users()
    {
        $post_data = [
            'authority' => 'authority',
            'username' => 'username',
            'email' => 'email@email.com',
            'display_name' => 'Display Name',
        ];
        $post_request = new Request(
            'POST',
            'users',
            ['Authorization' => $this->authorization, 'User-Agent' => 'HypothesisClient'],
            json_encode($post_data)
        );
        $post_response = new Response(405);
        $rejected_post_response = new RejectedPromise(new BadResponse('', $post_request, $post_response));
        $user = new User('username', 'email@email.com', 'Display Name');
        $this->usersClient;
        $this->httpClient
            ->expects($this->once())
            ->method('send')
            ->willReturn($rejected_post_response);
        $this->expectException(BadResponse::class);
        $this->users->upsert($user)->wait();
    }
}

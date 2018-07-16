<?php

namespace eLife\HypothesisClient\Client;

use eLife\HypothesisClient\ApiClient\UsersClient;
use eLife\HypothesisClient\Exception\BadResponse;
use eLife\HypothesisClient\Model\User;
use eLife\HypothesisClient\Result\Result;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function GuzzleHttp\Promise\rejection_for;

final class Users
{
    private $serializer;
    private $usersClient;

    public function __construct(UsersClient $usersClient, DenormalizerInterface $serializer)
    {
        $this->usersClient = $usersClient;
        $this->serializer = $serializer;
    }

    public function get(string $id) : PromiseInterface
    {
        return $this->usersClient
            ->getUser(
                [],
                $id
            )
            ->then(function (Result $result) {
                return $this->serializer->denormalize($result->toArray(), User::class);
            });
    }

    /**
     * Upsert the user by create first then, if user already detected, update.
     */
    public function upsert(User $user) : PromiseInterface
    {
        return $this->create($user)
            ->otherwise(function ($reason) use ($user) {
                if ($reason instanceof BadResponse) {
                    if (Response::HTTP_CONFLICT === $reason->getResponse()->getStatusCode()) {
                        // Probably means that the username already exists
                        return $this->update($user);
                    } elseif (Response::HTTP_BAD_REQUEST === $reason->getResponse()->getStatusCode()) {
                        // TODO remove when Hypothesis start returning 409 Conflict responses
                        return $this->update($user);
                    }
                }

                return rejection_for($reason);
            });
    }

    public function create(User $user) : PromiseInterface
    {
        return $this->usersClient
            ->createUser(
                [],
                $user->getUsername(),
                $user->getEmail(),
                $user->getDisplayName()
            )
            ->then(function (Result $result) {
                return $this->serializer->denormalize($result->toArray() + ['new' => true], User::class);
            });
    }

    public function update(User $user) : PromiseInterface
    {
        return $this->usersClient
            ->updateUser(
                [],
                $user->getUsername(),
                $user->getEmail(),
                $user->getDisplayName()
            )
            ->then(function (Result $result) {
                return $this->serializer->denormalize($result->toArray(), User::class, 'json');
            });
    }
}

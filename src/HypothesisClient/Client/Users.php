<?php

namespace eLife\HypothesisClient\Client;

use eLife\HypothesisClient\ApiClient\UsersClient;
use eLife\HypothesisClient\Exception\BadResponse;
use eLife\HypothesisClient\Model\User;
use eLife\HypothesisClient\Result\Result;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function GuzzleHttp\Promise\rejection_for;

final class Users
{
    private $normalizer;
    private $usersClient;

    public function __construct(UsersClient $usersClient, DenormalizerInterface $normalizer)
    {
        $this->usersClient = $usersClient;
        $this->normalizer = $normalizer;
    }

    public function get(string $id) : PromiseInterface
    {
        return $this->usersClient
            ->getUser(
                [],
                $id
            )
            ->then(function (Result $result) {
                return $this->normalizer->denormalize($result->toArray(), User::class);
            });
    }

    /**
     * Upsert the user by create first then, if user already detected, update.
     *
     * @param User $user
     *
     * @return PromiseInterface
     */
    public function upsert(User $user) : PromiseInterface
    {
        return $this->create($user)
            ->otherwise(function ($reason) use ($user) {
                /*
                 * The most likely cause of BadResponse is if the username
                 * already exists. Because this can only be determined by the
                 * text in the response, it is considered a bit fragile. Until
                 * Hypothesis set an error code in their response we will treat
                 * all BadResponse's as if they are for a known username and
                 * attempt an update request.
                 */
                if ($reason instanceof BadResponse) {
                    return $this->update($user);
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
                return $this->normalizer->denormalize($result->toArray() + ['new' => true], User::class);
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
                return $this->normalizer->denormalize($result->toArray(), User::class);
            });
    }
}

<?php

namespace eLife\HypothesisClient\Model;

use Assert\Assert;

final class User
{
    use CanBeNew;

    const USERNAME_MIN_LENGTH = 3;
    const USERNAME_MAX_LENGTH = 30;
    const DISPLAY_NAME_MIN_LENGTH = 1;
    const DISPLAY_NAME_MAX_LENGTH = 30;

    private $username;
    private $email;
    private $displayName;

    /**
     * @internal
     */
    public function __construct(
        string $username,
        string $email,
        string $displayName,
        bool $new = false
    ) {
        $this->username = $username;
        $this->email = $email;
        $this->displayName = $displayName;
        $this->new = $new;
        $this->validate();
    }

    public function getUsername() : string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getEmail() : string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getDisplayName() : string
    {
        return $this->displayName;
    }

    private function validate() : bool
    {
        return Assert::lazy()
            // Id must be between 3 and 30 characters.
            ->that($this->getUsername(), 'Username')
            ->betweenLength(self::USERNAME_MIN_LENGTH, self::USERNAME_MAX_LENGTH, 'Value "%s" must be between '.self::USERNAME_MIN_LENGTH.' and '.self::USERNAME_MAX_LENGTH.' characters.')
            // Id is limited to a small set of characters.
            ->that($this->getUsername(), 'Username')
            ->regex('/^[A-Za-z0-9._]+$/', 'Value "%s" does not match expression /^[A-Za-z0-9._]+$/.')
            ->that(array_filter([$this->getEmail(), $this->getDisplayName()]), 'User e-mail and display name')
            ->notEmpty('Either an e-mail address or display name is required.')
            // Email must be valid.
            ->that($this->getEmail(), 'User e-mail')
            ->nullOr()
            ->email()
            // Display name must be no more than 30 characters long.
            ->that($this->getDisplayName(), 'User display name')
            ->nullOr()
            ->betweenLength(self::DISPLAY_NAME_MIN_LENGTH, self::DISPLAY_NAME_MAX_LENGTH, 'Value "%s" must be between '.self::DISPLAY_NAME_MIN_LENGTH.' and '.self::DISPLAY_NAME_MAX_LENGTH.' characters.')
            ->verifyNow();
    }
}

<?php

namespace tests\eLife\HypothesisClient\Model;

use eLife\HypothesisClient\Model\User;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \eLife\HypothesisClient\Model\User
 */
final class UserTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_an_username()
    {
        $user = new User('username', 'email@email.com', 'Display Name');

        $this->assertSame('username', $user->getUsername());
    }

    /**
     * @test
     */
    public function it_has_an_email()
    {
        $user = new User('username', 'email@email.com', 'Display Name');

        $this->assertSame('username', $user->getUsername());
    }

    /**
     * @test
     * @dataProvider providerInvalidUsernames
     */
    public function it_rejects_invalid_usernames($username, $message = null)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->executeExceptionMessageRegExp($message);
        new User($username, 'email@email.com', 'display_name');
    }

    public function providerInvalidUsernames()
    {
        yield 'username too short' => ['aa', 'must be between 3 and 30 characters'];
        yield 'username too long' => ['zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz', 'must be between 3 and 30 characters'];
        yield 'username with spaces' => ['aa a', 'does not match expression'];
        yield 'username with invalid punctuation' => ['!!', ['must be between 3 and 30 characters', 'does not match expression']];
    }

    /**
     * @test
     * @dataProvider providerInvalidEmails
     */
    public function it_rejects_invalid_emails($email)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/was expected to be a valid e-mail address\./');
        new User('username', $email, 'display_name');
    }

    public function providerInvalidEmails()
    {
        yield 'email with spaces' => ['email@email. com'];
        yield 'email no @' => ['hostname.com'];
    }

    /**
     * @test
     * @dataProvider providerInvalidDisplayNames
     */
    public function it_rejects_invalid_display_names($method, $display_name, $message = null)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->executeExceptionMessageRegExp($message);
        new User('username', 'email@email.com', $display_name);
    }

    public function providerInvalidDisplayNames()
    {
        yield 'display_name too long' => ['zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz', 'must be between 1 and 30 characters.'];
    }

    /**
     * @test
     */
    public function it_collects_all_validation_errors()
    {
        $id = '!';
        $email = 'invalid';
        $display_name = 'This display name is too long!!';
        $messages = [
            '1) Username: Value "!" must be between 3 and 30 characters.',
            '2) Username: Value "!" does not match expression /^[A-Za-z0-9._]+$/.',
            '3) User e-mail: Value "invalid" was expected to be a valid e-mail address.',
            '4) User display name: Value "This display name is too long!!" must be between 1 and 30 characters.',
        ];
        $this->expectException(InvalidArgumentException::class);
        $this->executeExceptionMessageRegExp($messages);
        new User($id, $email, $display_name);
    }

    private function executeExceptionMessageRegExp($message = null, $glue = '.*\n.*')
    {
        if (!empty($message)) {
            $messages = array_map(function ($msg) {
                return preg_quote($msg, '/');
            }, (array) $message);
            $this->expectExceptionMessageRegExp('/'.implode($glue, $messages).'/');
        }
    }
}

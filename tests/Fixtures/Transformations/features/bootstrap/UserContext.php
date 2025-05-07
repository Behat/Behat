<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use PHPUnit\Framework\Assert;

class UserContext implements Context
{
    private User $user;

    private int $totalAge;

    #[Given('/I am (".*" user)/')]
    #[Given('I am user:')]
    #[Given('I am :user')]
    public function iAmUser(User $user): void
    {
        $this->user = $user;
    }

    #[Given('I am a user with this age:')]
    public function iAmAUserWithAge(array $data): void
    {
        $this->user = $data[0]['user'];
        $this->user->setAge($data[0]['age']);
    }

    #[Given('I am a Russian user with this age:')]
    public function iAmARussianUserWithAgeIn(array $data): void
    {
        $this->user = $data[0]['логин'];
        $this->user->setAge($data[0]['age']);
    }

    #[Given('I am a user with this hex age:')]
    public function iAmAUserWithHexAge(array $data): void
    {
        $this->user = $data[0]['user'];
        $this->user->setAge($data[0]['hex age']);
    }

    #[Given('I have two users and I add their ages')]
    public function addTwoUsersAges(array $data): void
    {
        $this->totalAge = $data[0]['user']->getAge();
        $this->totalAge += $data[0]['other user']->getAge();
    }

    #[Then('/Username must be "([^"]+)"/')]
    public function usernameMustBe(string $username): void
    {
        Assert::assertEquals($username, $this->user->getUsername());
    }

    #[Then('/Age must be (\d+)/')]
    public function ageMustBe(string $age): void
    {
        Assert::assertEquals($age, $this->user->getAge());
    }

    #[Then('/total age must be (\d+)/')]
    public function totalAgeMustBe(int $age): void
    {
        Assert::assertEquals($age, $this->totalAge);
    }

    #[Then('the Usernames must be:')]
    public function usernamesMustBe(array $usernames): void
    {
        Assert::assertEquals($usernames[0], $this->user->getUsername());
    }

    #[Then('/^the boolean (no) should be transformed to false$/')]
    public function theBooleanShouldBeTransformed(bool $boolean): void
    {
        Assert::assertSame(false, $boolean);
    }
}

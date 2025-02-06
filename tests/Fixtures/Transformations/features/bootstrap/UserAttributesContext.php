<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use PHPUnit\Framework\Assert;

class UserAttributesContext implements Context
{
    private User $user;

    #[Given('/I am (".*" user)/')]
    #[Given('I am :user')]
    public function iAmUser(User $user): void
    {
        $this->user = $user;
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

    #[Then('/^the boolean (no) should be transformed to false$/')]
    public function theBooleanShouldBeTransformed(bool $boolean): void
    {
        Assert::assertSame(false, $boolean);
    }
}

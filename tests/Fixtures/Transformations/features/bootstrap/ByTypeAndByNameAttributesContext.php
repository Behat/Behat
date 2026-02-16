<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Transformation\Transform;

class ByTypeAndByNameAttributesContext implements Context
{
    private User $I;
    private User $she;

    #[Transform]
    public function userFromName(string $name): User
    {
        return new User($name);
    }

    #[Transform(':admin')]
    public function adminFromName(string $name): User
    {
        return new User('admin: ' . $name);
    }

    #[Transform(':admin')]
    public function adminString(): string
    {
        return 'admin';
    }

    #[Given('I am :user')]
    public function iAm(User $user): void
    {
        $this->I = $user;
    }

    #[Given('she is :admin')]
    public function sheIs(User $admin): void
    {
        $this->she = $admin;
    }

    #[Then('I should be a user named :name')]
    public function iShouldHaveName(string $name): void
    {
        if ($name !== $this->I->getUsername()) {
            throw new Exception("My actual name is {$this->I->getUsername()}");
        }
    }

    #[Then('she should be an admin named :name')]
    public function sheShouldHaveName(string $name): void
    {
        if ($name !== $this->she->getUsername()) {
            throw new Exception("Her actual name is {$this->she->getUsername()}");
        }
    }
}

<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Transformation\Transform;

class ByTypeAttributesContext implements Context
{
    private User $I;
    private User $he;

    #[Transform]
    public function userFromName(string $name): User
    {
        return new User($name);
    }

    #[Given('I am :user')]
    public function iAm(User $user): void
    {
        $this->I = $user;
    }

    #[Given('/^he is "([^"]+)"$/')]
    public function heIs(User $user): void
    {
        $this->he = $user;
    }

    #[Then('I should be a user named :name')]
    public function iShouldHaveName(string $name): void
    {
        if ($name !== $this->I->getUsername()) {
            throw new Exception("My actual name is {$this->I->getUsername()}");
        }
    }

    #[Then('he should be a user named :name')]
    public function heShouldHaveName(string $name): void
    {
        if ($name !== $this->he->getUsername()) {
            throw new Exception("His actual name is {$this->he->getUsername()}");
        }
    }
}

<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Transformation\Transform;

class ByTypeUnionAttributesContext implements Context
{
    #[Transform]
    public function userFromName(string $name): User|int
    {
        return $name === 'nobody' ? 0 : new User($name);
    }

    #[Given('I am :user')]
    #[Given('she is :user')]
    public function iAm(User $user): void
    {
    }
}

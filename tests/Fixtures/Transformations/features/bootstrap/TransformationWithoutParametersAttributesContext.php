<?php

use Behat\Behat\Context\Context;
use Behat\Transformation\Transform;

class TransformationWithoutParametersAttributesContext implements Context
{
    #[Transform]
    public function userFromName(string $username): User
    {
        return new User($username);
    }
}

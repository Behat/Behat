<?php

use Behat\Behat\Context\Context;
use Behat\Transformation\Transform;

class MultipleTransformationsInOneFunctionAttributesContext implements Context
{
    #[Transform('/"([^\ "]+)(?: - (\d+))?" user/')]
    #[Transform(':user')]
    public function createUserFromUsername(string $username, int $age = 20): User
    {
        return new User($username, $age);
    }
}

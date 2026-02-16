<?php

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Behat\Transformation\Transform;
use PHPUnit\Framework\Assert;

class ScalarTypeAttributesContext implements Context
{
    #[Transform]
    public function transformToUser(string $name): User
    {
        return new User($name);
    }

    #[Then(':string should be passed')]
    public function checkStringIsPassed(string $value): void
    {
        Assert::assertIsString($value);
    }
}

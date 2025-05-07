<?php

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

class ScalarTypeAnnotationsContext implements Context
{
    /**
     * @Transform
     */
    public function transformToUser(string $name): User
    {
        return new User($name);
    }

    /**
     * @Then :string should be passed
     */
    public function checkStringIsPassed(string $value): void
    {
        Assert::assertIsString($value);
    }
}

<?php

use Behat\Behat\Context\Context;

abstract class BaseContext implements Context
{
    private static bool $thrown = false;

    protected static function throwFailure(string $message): void
    {
        if (self::$thrown) {
            return;
        }

        self::$thrown = true;

        throw new Exception($message);
    }
}

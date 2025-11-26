<?php

use Behat\Behat\Context\Context;

class NullArgsContext implements Context
{
    public function __construct($name, Service1 $s1, Service2 $s2, Service3 $s3)
    {
        PHPUnit\Framework\Assert::assertNull($name);
    }

    /** @Given a step */
    public function aStep()
    {
    }
}

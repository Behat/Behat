<?php

use Behat\Behat\Context\Context;

class ConstructorArgsContext implements Context
{
    public function __construct(Service2 $s1, Service1 $s2, Service3 $s3)
    {
    }

    /** @Given a step */
    public function aStep()
    {
    }
}

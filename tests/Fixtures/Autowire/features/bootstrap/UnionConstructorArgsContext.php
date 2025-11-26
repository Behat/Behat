<?php

use Behat\Behat\Context\Context;

class UnionConstructorArgsContext implements Context
{
    public function __construct(Service1|Service2 $s)
    {
    }

    /** @Given a step */
    public function aStep()
    {
    }
}

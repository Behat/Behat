<?php

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Tests\Fixtures\Assert;

class MixedConstructorArgsContext implements Context
{
    public function __construct(Service2 $s2, $name, Service1 $s1, Service3 $s3)
    {
        Assert::assertEquals('Konstantin', $name);
    }

    #[Given('a step')]
    public function aStep()
    {
    }
}

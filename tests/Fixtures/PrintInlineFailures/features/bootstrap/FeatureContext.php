<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Step\Given;

class FeatureContext implements Context
{
    #[Given('a passing step')]
    public function aPassingStep(): void
    {
    }

    #[Given('a failing step')]
    public function aFailingStep(): void
    {
        throw new RuntimeException('step failed as supposed');
    }

    #[Given('a pending step')]
    public function aPendingStep(): void
    {
        throw new PendingException();
    }
}

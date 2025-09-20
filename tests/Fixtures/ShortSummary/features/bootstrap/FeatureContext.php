<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Hook\AfterSuite;
use Behat\Step\Given;

class FeatureContext implements Context
{
    #[Given('I have a passing step')]
    public function iHaveAPassingStep()
    {
    }

    #[Given('I have a pending step')]
    public function iHaveAPendingStep()
    {
        throw new PendingException();
    }

    #[Given('I have a failing step')]
    public function iHaveAFailingStep()
    {
        throw new Exception('This scenario has a failed step');
    }

    #[AfterSuite]
    public static function tearDown()
    {
        throw new Exception('This suite has a failed teardown');
    }
}

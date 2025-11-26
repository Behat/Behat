<?php

use Behat\Hook\AfterStep;
use Behat\Hook\BeforeStep;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class StepHookAttributesContext implements Behat\Behat\Context\Context
{
    #[BeforeStep]
    public function beforeStep()
    {
        echo '= BEFORE STEP =';
    }

    #[AfterStep]
    public function afterStep()
    {
        echo '= AFTER STEP =';
    }

    #[BeforeStep('I have 3 apples')]
    public function beforeApples()
    {
        echo '= BEFORE APPLES =';
    }

    #[AfterStep('I have 3 apples')]
    public function afterApples()
    {
        echo '= AFTER APPLES =';
    }

    #[Given('I have :count apple(s)')]
    #[Given('I have :count banana(s)')]
    public function iHaveFruit($count)
    {
    }

    #[When('I eat :count apple(s)')]
    #[When('I eat :count banana(s)')]
    public function iEatFruit($count)
    {
    }

    #[Then('I should have :count apple(s)')]
    #[Then('I should have :count banana(s)')]
    public function iShouldHaveFruit($count)
    {
    }
}

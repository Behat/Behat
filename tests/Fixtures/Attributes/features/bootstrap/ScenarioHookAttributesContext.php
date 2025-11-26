<?php

use Behat\Hook\AfterScenario;
use Behat\Hook\BeforeScenario;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class ScenarioHookAttributesContext implements Behat\Behat\Context\Context
{
    #[BeforeScenario]
    public function beforeScenario()
    {
        echo '= BEFORE SCENARIO =';
    }

    #[BeforeScenario('@bananas')]
    public function beforeBananas()
    {
        echo '= BEFORE BANANAS =';
    }

    #[AfterScenario]
    public function afterScenario()
    {
        echo '= AFTER SCENARIO =';
    }

    #[AfterScenario('@bananas')]
    public function afterBananas()
    {
        echo '= AFTER BANANAS =';
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

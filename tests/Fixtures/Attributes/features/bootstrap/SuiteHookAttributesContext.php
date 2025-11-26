<?php

use Behat\Hook\AfterSuite;
use Behat\Hook\BeforeSuite;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class SuiteHookAttributesContext implements Behat\Behat\Context\Context
{
    #[BeforeSuite]
    public static function beforeSuite()
    {
        echo '= BEFORE SUITE =';
    }

    #[BeforeSuite('apples')]
    public static function beforeSuiteApples()
    {
        echo '= BEFORE APPLES =';
    }

    #[AfterSuite]
    public static function afterSuite()
    {
        echo '= AFTER SUITE =';
    }

    #[AfterSuite('apples')]
    public static function afterSuiteApples()
    {
        echo '= AFTER APPLES =';
    }

    #[Given('I have :count apple(s)')]
    public function iHaveFruit($count)
    {
    }

    #[When('I eat :count apple(s)')]
    public function iEatFruit($count)
    {
    }

    #[Then('I should have :count apple(s)')]
    public function iShouldHaveFruit($count)
    {
    }
}

<?php

use Behat\Hook\AfterFeature;
use Behat\Hook\BeforeFeature;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class FeatureHookAttributesContext implements Behat\Behat\Context\Context
{
    #[BeforeFeature]
    public static function beforeFeature()
    {
        echo '= BEFORE FEATURE =';
    }

    #[AfterFeature]
    public static function afterFeature()
    {
        echo '= AFTER FEATURE =';
    }

    #[BeforeFeature('Fruit story')]
    public static function beforeFruitStory()
    {
        echo '= BEFORE FRUIT STORY =';
    }

    #[AfterFeature('Fruit story')]
    public static function afterFruitStory()
    {
        echo '= AFTER FRUIT STORY =';
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

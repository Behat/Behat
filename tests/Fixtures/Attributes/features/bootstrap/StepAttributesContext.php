<?php

use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class StepAttributesContext implements Behat\Behat\Context\Context
{
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

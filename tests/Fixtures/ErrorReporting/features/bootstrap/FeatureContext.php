<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Exception\PendingException;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class FeatureContext implements Context
{
    private $array;
    private $result;

    #[Given('/^I have an empty array$/')]
    public function iHaveAnEmptyArray()
    {
        $this->array = [];
    }

    #[When('/^I access array index (\d+)$/')]
    public function iAccessArrayIndex($arg1)
    {
        $index = intval($arg1);
        $this->result = $this->array[$index];
    }

    #[Then('/^I should get NULL$/')]
    public function iShouldGetNull()
    {
        PHPUnit\Framework\Assert::assertNull($this->result);
    }

    #[When('/^I push "([^"]*)" to that array$/')]
    public function iPushToThatArray($arg1)
    {
        array_push($this->array, $arg1);
    }

    #[Then('/^I should get "([^"]*)"$/')]
    public function iShouldGet($arg1)
    {
        PHPUnit\Framework\Assert::assertEquals($arg1, $this->result);
    }

    #[When('an exception is thrown')]
    public function anExceptionIsThrown()
    {
        throw new \Exception('Exception is thrown');
    }

    #[When('I trim NULL')]
    public function iTrimNull()
    {
        trim(null);
    }
}

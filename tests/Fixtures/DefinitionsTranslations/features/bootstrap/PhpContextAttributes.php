<?php

use Behat\Behat\Context\TranslatableContext;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Transformation\Transform;

class PhpContextAttributes implements TranslatableContext
{
    private $numbers = [];
    private $result = 0;

    #[Given('/^I have entered (\d+) into calculator$/')]
    public function iHaveEnteredIntoCalculator($number)
    {
        $this->numbers[] = intval($number);
    }

    #[Given('/^I have clicked "+"$/')]
    public function iHaveClickedPlus()
    {
        $this->result = array_sum($this->numbers);
    }

    #[Then('/^I should see (\d+) on the screen$/')]
    public function iShouldSeeOnTheScreen($result)
    {
        PHPUnit\Framework\Assert::assertEquals(intval($result), $this->result);
    }

    #[Transform('/"([^"]+)" user/')]
    public static function createUserFromUsername($username)
    {
        return (object) ['name' => $username];
    }

    #[Then('/^the ("[^"]+" user) name should be "([^"]*)"$/')]
    public function theUserUsername($user, $username)
    {
        PHPUnit\Framework\Assert::assertEquals($username, $user->name);
    }

    public static function getTranslationResources()
    {
        return [__DIR__ . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . 'ru.php'];
    }
}

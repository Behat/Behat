<?php

use Behat\Behat\Context\TranslatableContext;
use Behat\Step\Given;

class TranslatedDefinitionsContext implements TranslatableContext
{
    #[Given('/^I have entered (\d+) into calculator$/')]
    public function iHaveEnteredIntoCalculator($number)
    {
    }

    #[Given('/^I have clicked "+"$/')]
    public function iHaveClickedPlus()
    {
    }

    public static function getTranslationResources()
    {
        return array(__DIR__ . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . 'ru.xliff');
    }
}

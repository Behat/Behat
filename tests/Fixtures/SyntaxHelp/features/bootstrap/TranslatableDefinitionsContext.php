<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Behat\Context\TranslatableContext;
use Behat\Behat\Exception\PendingException;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class TranslatableDefinitionsContext implements Context, TranslatableContext
{
    #[Given('/^I have (\\d+) apples?$/')]
    public function iHaveApples($count): void
    {
        throw new PendingException();
    }

    #[When('/^I ate (\\d+) apples?$/')]
    public function iAteApples($count): void
    {
        throw new PendingException();
    }

    #[When('/^I found (\\d+) apples?$/')]
    public function iFoundApples($count): void
    {
        throw new PendingException();
    }

    #[Then('/^I should have (\\d+) apples$/')]
    public function iShouldHaveApples($count): void
    {
        throw new PendingException();
    }

    public static function getTranslationResources(): array
    {
        return [__DIR__ . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . 'ru.xliff'];
    }
}

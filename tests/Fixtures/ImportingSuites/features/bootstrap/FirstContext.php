<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;

final class FirstContext implements Context
{
    /**
     * @Given I have :count apple(s)
     */
    public function iHaveApples(int $count): void
    {
    }

    /**
     * @When I ate :count apple(s)
     */
    public function iAteApples(int $count): void
    {
    }

    /**
     * @Then I should have :count apple(s)
     */
    public function iShouldHaveApples(int $count): void
    {
    }
}

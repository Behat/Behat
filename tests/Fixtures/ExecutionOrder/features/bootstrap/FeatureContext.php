<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Step\Given;

final class FeatureContext implements Context
{
    #[Given('I have :num orange(s)')]
    public function iHaveOranges(int $num): void
    {
    }
}

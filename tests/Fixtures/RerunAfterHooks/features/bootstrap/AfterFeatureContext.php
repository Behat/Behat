<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Hook\AfterFeature;

class AfterFeatureContext implements Context
{
    #[AfterFeature('@failing-after-feature-hook')]
    public static function failAfterFeature(): void
    {
        throw new RuntimeException('after feature hook failure');
    }
}

<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

class FeatureContext implements Context
{
    #[Given('I call a step used in the first feature')]
    public function stepUsedInFirstFeature(): void
    {
    }

    #[When('I call a step used in both features')]
    public function stepUsedInBothFeatures(): void
    {
    }

    #[When('I call a pending step')]
    public function pendingStep(): void
    {
        throw new PendingException();
    }

    #[When('I call a skipped step')]
    public function skippedStep(): void
    {
    }

    #[Then('I call a step used in the second feature')]
    public function stepUsedInSecondFeature(): void
    {
    }

    /**
     * This is a step that is never used and should be removed.
     */
    #[Then('I call a step not used in any feature')]
    public function stepNotUsedInAnyFeature(): void
    {
    }
}

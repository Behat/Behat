<?php

use Behat\Behat\Context\Context;
use Behat\Hook\BeforeScenario;
use Behat\Step\Given;
use Behat\Step\Then;

class FeatureContext implements Context
{
    #[BeforeScenario('@deprecation-in-hook')]
    public function beforeScenario(): void
    {
        @trigger_error('Deprecation triggered in hook', E_USER_DEPRECATED);
    }

    #[Given('I run a step')]
    public function iRunAStep(): void
    {
    }

    #[Given('I run a step that triggers a deprecation')]
    public function iRunAStepThatTriggersADeprecation(): void
    {
        @trigger_error('Deprecation triggered in step definition', E_USER_DEPRECATED);
    }

    #[Then('the step should pass')]
    public function theStepShouldPass(): void
    {
    }
}

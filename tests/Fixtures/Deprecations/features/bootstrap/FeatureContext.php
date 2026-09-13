<?php

use Behat\Behat\Context\Context;
use Behat\Hook\BeforeScenario;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Testwork\Deprecation\DeprecationCollector;

class FeatureContext implements Context
{
    #[BeforeScenario('@deprecation-in-hook')]
    public function beforeScenario(): void
    {
        DeprecationCollector::trigger('Deprecation triggered in hook');
    }

    #[Given('I run a step')]
    public function iRunAStep(): void
    {
    }

    #[Given('I run a step that triggers a deprecation')]
    public function iRunAStepThatTriggersADeprecation(): void
    {
        DeprecationCollector::trigger('Deprecation triggered in step definition');
    }

    #[Given('I run a step that triggers an unsuppressed deprecation')]
    public function iRunAStepThatTriggersAnUnsuppressedDeprecation(): void
    {
        trigger_error('Deprecation triggered in step definition', E_USER_DEPRECATED);
    }

    #[Then('the step should pass')]
    public function theStepShouldPass(): void
    {
    }
}

<?php

use Behat\Hook\BeforeScenario;

final class BeforeScenarioContext extends BaseContext
{
    #[BeforeScenario]
    public function beforeScenarioHook(): void
    {
        self::throwFailure('Error in beforeScenario hook');
    }
}

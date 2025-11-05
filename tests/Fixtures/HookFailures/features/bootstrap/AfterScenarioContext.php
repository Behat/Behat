<?php

use Behat\Hook\AfterScenario;

final class AfterScenarioContext extends BaseContext
{
    #[AfterScenario]
    public function afterScenarioHook(): void
    {
        self::throwFailure('Error in afterScenario hook');
    }
}

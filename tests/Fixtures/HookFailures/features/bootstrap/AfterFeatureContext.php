<?php

use Behat\Hook\AfterFeature;

final class AfterFeatureContext extends BaseContext
{
    #[AfterFeature]
    public static function afterFeatureHook(): void
    {
        self::throwFailure('Error in afterFeature hook');
    }
}

<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withSuite(
                (new Suite('default'))
                    ->withPaths('features/scenario_hook.feature')
                    ->withContexts('FeatureContext')
            )
    )
    ->withProfile(
        (new Profile('afterFeature'))
            ->withSuite(
                (new Suite('default'))
                    ->withPaths('features/feature_hook.feature', 'features/first.feature')
                    ->withContexts('FeatureContext', 'AfterFeatureContext')
            )
    )
;

<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withSuite(
                (new Suite('failing'))
                    ->withPaths('features/shared.feature')
                    ->addContext('FeatureContext', ['shouldFail' => true])
            )
            ->withSuite(
                (new Suite('passing'))
                    ->withPaths('features/shared.feature')
                    ->addContext('FeatureContext', ['shouldFail' => false])
            )
    )
    ->withProfile(
        (new Profile('afterSuite'))
            ->withSuite(
                (new Suite('failing'))
                    ->withPaths('features/shared.feature')
                    ->addContext('FeatureContext', ['shouldFail' => false])
                    ->withContexts('AfterSuiteContext')
            )
            ->withSuite(
                (new Suite('passing'))
                    ->withPaths('features/shared.feature')
                    ->addContext('FeatureContext', ['shouldFail' => false])
            )
    )
;

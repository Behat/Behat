<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile((new Profile('beforeSuite'))
        ->withSuite((new Suite('default'))
            ->withContexts(
                'BeforeSuiteContext',
                'SimpleStepContext'
            )
        )
    )
    ->withProfile((new Profile('afterSuite'))
        ->withSuite((new Suite('default'))
            ->withContexts(
                'AfterSuiteContext',
                'SimpleStepContext'
            )
        )
    )
    ->withProfile((new Profile('beforeFeature'))
        ->withSuite((new Suite('default'))
            ->withContexts(
                'BeforeFeatureContext',
                'SimpleStepContext'
            )
        )
    )
    ->withProfile((new Profile('afterFeature'))
        ->withSuite((new Suite('default'))
            ->withContexts(
                'AfterFeatureContext',
                'SimpleStepContext'
            )
        )
    )
    ->withProfile((new Profile('beforeScenario'))
        ->withSuite((new Suite('default'))
            ->withContexts(
                'BeforeScenarioContext',
                'SimpleStepContext'
            )
        )
    )
    ->withProfile((new Profile('afterScenario'))
        ->withSuite((new Suite('default'))
            ->withContexts(
                'AfterScenarioContext',
                'SimpleStepContext'
            )
        )
    )
    ->withProfile((new Profile('beforeStep'))
        ->withSuite((new Suite('default'))
            ->withContexts(
                'BeforeStepContext',
                'SimpleStepContext'
            )
        )
    )
    ->withProfile((new Profile('afterStep'))
        ->withSuite((new Suite('default'))
            ->withContexts(
                'AfterStepContext',
                'SimpleStepContext'
            )
        )
    )
;

<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile((new Profile('step_attributes'))
        ->withSuite(new Suite('default', ['contexts' => ['StepAttributesContext']]))
    )
    ->withProfile((new Profile('feature_hooks'))
        ->withSuite(new Suite('default', ['contexts' => ['FeatureHookAttributesContext']]))
    )
    ->withProfile((new Profile('scenario_hooks'))
        ->withSuite(new Suite('default', ['contexts' => ['ScenarioHookAttributesContext']]))
    )
    ->withProfile((new Profile('suite_hooks'))
        ->withSuite(new Suite('apples', ['contexts' => ['SuiteHookAttributesContext']]))
    )
    ->withProfile((new Profile('step_hooks'))
        ->withSuite(new Suite('default', ['contexts' => ['StepHookAttributesContext']]))
    );

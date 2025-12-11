<?php

use Behat\Config\Config;
use Behat\Config\Formatter\ProgressFormatter;
use Behat\Config\Formatter\ShowOutputOption;
use Behat\Config\Profile;
use Behat\Config\Suite;

$defaultProfile = (new Profile('default'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('FeatureContext')
    )
;

$progressNoOutputProfile = (new Profile('progress_no_output'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('FeatureContext')
    )
    ->withFormatter(new ProgressFormatter(showOutput: ShowOutputOption::No))
;

return (new Config())
    ->withProfile($defaultProfile)
    ->withProfile($progressNoOutputProfile)
;

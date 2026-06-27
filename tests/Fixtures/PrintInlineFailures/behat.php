<?php

use Behat\Config\Config;
use Behat\Config\Formatter\ProgressFormatter;
use Behat\Config\Profile;
use Behat\Config\Suite;

$defaultProfile = (new Profile('default'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('FeatureContext')
    )
;

$inlineProfile = (new Profile('inline'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('FeatureContext')
    )
    ->withFormatter(new ProgressFormatter(inlineFailures: true))
;

return (new Config())
    ->withProfile($defaultProfile)
    ->withProfile($inlineProfile)
;

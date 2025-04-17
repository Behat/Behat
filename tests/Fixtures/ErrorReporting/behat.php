<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\TesterOptions;

return (new Config())
    ->withProfile(new Profile('default'))
    ->withProfile((new Profile('ignore-all-but-error'))
        ->withTesterOptions((new TesterOptions())
            ->withErrorReporting(24565)))
    ->withProfile((new Profile('ignore-deprecations'))
        ->withTesterOptions((new TesterOptions())
            ->withErrorReporting(22527)));

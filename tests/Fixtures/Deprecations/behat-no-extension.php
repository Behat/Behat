<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Behat\Config\TesterOptions;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withTesterOptions(
                (new TesterOptions())->withPrintBehatDeprecations()
            )
            ->withSuite(
                (new Suite('default'))
                    ->withPaths('features/deprecations_in_steps.feature')
            )
    );

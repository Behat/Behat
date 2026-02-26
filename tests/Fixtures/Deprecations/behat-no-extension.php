<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withPrintBehatDeprecations()
            ->withSuite(
                (new Suite('default'))
                    ->withPaths('features/deprecations_in_steps.feature')
            )
    );

<?php

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withExtension(new Extension('deprecation_extension.php'))
            ->withSuite(
                (new Suite('default'))
                    ->withPaths('features/deprecations.feature')
            )
    )
    ->withProfile(
        (new Profile('print_behat_deprecations'))
            ->withExtension(new Extension('deprecation_extension.php'))
            ->withPrintBehatDeprecations()
            ->withSuite(
                (new Suite('print_behat_deprecations'))
                    ->withPaths('features/deprecations.feature')
            )
    )
    ->withProfile(
        (new Profile('deprecations_in_steps'))
            ->withPrintBehatDeprecations()
            ->withSuite(
                (new Suite('deprecations_in_steps'))
                    ->withPaths('features/deprecations_in_steps.feature')
            )
    );

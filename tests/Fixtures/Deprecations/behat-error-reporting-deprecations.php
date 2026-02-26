<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Behat\Config\TesterOptions;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withTesterOptions(
                (new TesterOptions())->withErrorReporting(E_USER_DEPRECATED)
            )
            ->withSuite(
                (new Suite('default'))
                    ->withPaths('features/unsuppressed_deprecation.feature')
            )
    );

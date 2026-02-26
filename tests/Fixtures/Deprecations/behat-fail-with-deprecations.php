<?php

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Behat\Config\TesterOptions;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withExtension(new Extension('deprecation_extension.php'))
            ->withTesterOptions(
                (new TesterOptions())->withFailOnBehatDeprecations()
            )
            ->withSuite(
                (new Suite('default'))
                    ->withPaths('features/deprecations.feature')
            )
    );

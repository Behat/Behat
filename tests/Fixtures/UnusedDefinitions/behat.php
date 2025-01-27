<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile((new Profile('default'))
        ->withSuite((new Suite('first_suite'))
            ->withPaths(
                'features/first_feature.feature'
            )
        )
        ->withSuite((new Suite('second_suite'))
            ->withPaths(
                'features/second_feature.feature',
            )
        )
    );

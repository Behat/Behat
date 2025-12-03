<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withSuite(
                (new Suite('default'))
                    ->addContext('FeatureContext')
            )
    )
    ->withProfile(
        (new Profile('isolation'))
            ->withSuite(
                (new Suite('default'))
                    ->addContext('FeatureContextIsolation')
            )
    );

<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('default'))->withSuite(
            (new Suite('default'))
                ->addContext('FeatureContextPackage')
        )
    )
;

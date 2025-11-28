<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile((new Profile('inherit'))
        ->withSuite(new Suite('default', [
            'contexts' => ['FeatureContextInheritAnnotations'],
        ]))
    )
    ->withProfile((new Profile('both_patterns'))
        ->withSuite(new Suite('default', [
            'contexts' => ['FeatureContextBothAnnotations'],
        ]))
    );

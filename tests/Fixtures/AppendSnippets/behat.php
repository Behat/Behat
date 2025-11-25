<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(new Profile('default'))
    ->withProfile((new Profile('no_pending_exception'))
        ->withSuite(new Suite('default', ['contexts' => ['FeatureContextNoPendingException']]))
    )
    ->withProfile((new Profile('minimal_imports'))
        ->withSuite(new Suite('default', ['contexts' => ['FeatureContextMinimalImports']]))
    )
    ->withProfile((new Profile('multicontext'))
        ->withSuite(new Suite('first', ['contexts' => ['FirstContext']]))
        ->withSuite(new Suite('second', ['contexts' => ['SecondContext']]))
    );

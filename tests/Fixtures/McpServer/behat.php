<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile((new Profile('default'))
        ->withSuite(new Suite('default', [
            'contexts' => ['FeatureContext'],
        ]))
        ->withSuite(new Suite('calculator', [
            'contexts' => ['FeatureContext'],
            'paths' => ['features/calculator.feature'],
        ]))
        ->withSuite(new Suite('greeting', [
            'contexts' => ['FeatureContext'],
            'paths' => ['features/greeting.feature'],
        ]))
    )
    ->withProfile((new Profile('calculator'))
        ->withSuite(new Suite('default', [
            'contexts' => ['FeatureContext'],
            'paths' => ['features/calculator.feature'],
        ]))
    )
    ->withProfile((new Profile('greeting'))
        ->withSuite(new Suite('default', [
            'contexts' => ['FeatureContext'],
            'paths' => ['features/greeting.feature'],
        ]))
    );

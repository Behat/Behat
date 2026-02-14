<?php

use Behat\Config\Config;
use Behat\Config\Profile;

// This config intentionally has invalid paths configuration
// The paths should be an array, but we're passing a string directly
// via the raw settings array to test error handling
return (new Config())
    ->withProfile(
        new Profile('default', [
            'suites' => [
                'first' => [
                    'paths' => '%paths.base%/features/first',
                    'contexts' => ['FirstContext'],
                ],
                'second' => [
                    'paths' => ['%paths.base%/features/second'],
                    'contexts' => ['SecondContext'],
                ],
            ],
        ])
    );

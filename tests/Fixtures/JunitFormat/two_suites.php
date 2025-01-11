<?php

use Behat\Config\Config;

return new Config([
    'default' => [
        'suites' => [
            'small_kid' => [
                'paths' => [
                    '%paths.base%/features/multiple_suites_1.feature',
                    '%paths.base%/features/multiple_suites_2.feature',
                ],
                'filters' => [
                    'role' => 'small kid'
                ],
                'contexts' => [
                    MultipleSuites1Context::class,
                ]
            ],
            'old_man' => [
                'paths' => [
                    '%paths.base%/features/multiple_suites_1.feature',
                    '%paths.base%/features/multiple_suites_2.feature',
                ],
                'filters' => [
                    'role' => 'old man'
                ],
                'contexts' => [
                    MultipleSuites2Context::class,
                ]
            ],
        ]
    ]
]);

<?php

use Behat\Config\Config;

return new Config([
    'default' => [
        'suites' => [
            'single_feature' => [
                'paths' => [
                    '%paths.base%/features/single_feature.feature',
                ],
                'contexts' => [
                    SingleFeatureContext::class,
                ]
            ],
            'multiple_features' => [
                'paths' => [
                    '%paths.base%/features/multiple_features_1.feature',
                    '%paths.base%/features/multiple_features_2.feature',
                ],
                'contexts' => [
                    MultipleFeaturesContext::class,
                ]
            ],
            'multiline_titles' => [
                'paths' => [
                    '%paths.base%/features/multiline_titles.feature',
                ],
                'contexts' => [
                    MultilineTitlesContext::class,
                ]
            ],
            'skipped_test_cases' => [
                'paths' => [
                    '%paths.base%/features/skipped_test_cases.feature',
                ],
                'contexts' => [
                    SkippedTestCasesContext::class,
                ]
            ],
            'stop_on_failure' => [
                'paths' => [
                    '%paths.base%/features/stop_on_failure.feature',
                ],
                'contexts' => [
                    StopOnFailureContext::class,
                ]
            ],
            'abort_on_php_error' => [
                'paths' => [
                    '%paths.base%/features/abort_on_php_error.feature',
                ],
                'contexts' => [
                    AbortOnPHPErrorContext::class
                ]
            ],
        ]
    ]
]);

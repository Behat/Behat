<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('default'))
        ->withSuite(
            (new Suite('single_feature'))
            ->withPaths(
                'features/single_feature.feature'
            )
        )
        ->withSuite(
            (new Suite('multiple_features'))
            ->withPaths(
                'features/multiple_features_1.feature',
                'features/multiple_features_2.feature'
            )
        )
        ->withSuite(
            (new Suite('multiline_titles'))
            ->withPaths(
                'features/multiline_titles.feature'
            )
        )
        ->withSuite(
            (new Suite('skipped_test_cases'))
            ->withPaths(
                'features/skipped_test_cases.feature'
            )
        )
        ->withSuite(
            (new Suite('stop_on_failure'))
            ->withPaths(
                'features/stop_on_failure.feature'
            )
        )
        ->withSuite(
            (new Suite('abort_on_php_error'))
            ->withPaths(
                'features/abort_on_php_error.feature'
            )
        )
    );

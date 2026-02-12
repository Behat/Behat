<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

return (new Config())
    ->withProfile(
        (new Profile('attributes'))
        ->withSuite(
            (new Suite('xliff'))
            ->withPaths('features/calc_ru.feature')
            ->withContexts('FeatureContextAttributes')
        )
        ->withSuite(
            (new Suite('yaml'))
            ->withPaths('features/calc_ru.feature')
            ->withContexts('YamlContextAttributes')
        )
        ->withSuite(
            (new Suite('php'))
            ->withPaths('features/calc_ru.feature')
            ->withContexts('PhpContextAttributes')
        )
        ->withSuite(
            (new Suite('arguments'))
            ->withPaths('features/calc_ru_arguments.feature')
            ->withContexts('ArgumentsContextAttributes')
        )
    )
;

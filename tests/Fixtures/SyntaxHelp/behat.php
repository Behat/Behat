<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\Suite;

$profile = (new Profile('default'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('BasicContext')
    )
;

$definitions = (new Profile('definitions'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('DefinitionsContext')
    )
;

$translatable = (new Profile('translatable'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('TranslatableDefinitionsContext')
    )
;

$descriptions = (new Profile('descriptions'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('DescriptionsContext')
    )
;

return (new Config())
    ->withProfile($profile)
    ->withProfile($definitions)
    ->withProfile($translatable)
    ->withProfile($descriptions)
;

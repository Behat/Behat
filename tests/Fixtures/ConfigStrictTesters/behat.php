<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\TesterOptions;

return (new Config())
    ->withProfile(new Profile('default'))
    ->withProfile((new Profile('with-strict'))
        ->withTesterOptions((new TesterOptions())
            ->withStrictResultInterpretation()))
    ->withProfile((new Profile('not-strict'))
        ->withTesterOptions((new TesterOptions())
            ->withStrictResultInterpretation(false)));

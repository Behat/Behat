<?php

use Behat\Config\Config;
use Behat\Config\Profile;
use Behat\Config\TesterOptions;

return (new Config())
    ->withProfile(new Profile('default'))
    ->withProfile((new Profile('stop-on-failure'))
        ->withTesterOptions((new TesterOptions())
            ->withStopOnFailure(true)))
    ->withProfile((new Profile('no-stop-on-failure'))
        ->withTesterOptions((new TesterOptions())
            ->withStopOnFailure(false)));

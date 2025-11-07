<?php

use Behat\Config\Config;
use Behat\Config\Formatter\PrettyFormatter;
use Behat\Config\Formatter\ProgressFormatter;
use Behat\Config\Profile;

return (new Config())
    ->withProfile(new Profile('default'))
    ->withProfile((new Profile('pretty_with_long_summary'))
        ->withFormatter(new PrettyFormatter(
            shortSummary: false,
            timer: false
        ))
    )
    ->withProfile((new Profile('progress_with_short_summary'))
        ->withFormatter(new ProgressFormatter(
            shortSummary: true,
            timer: false
        ))
    )
;

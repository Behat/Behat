<?php

use Behat\Config\Config;
use Behat\Config\Formatter\PrettyFormatter;
use Behat\Config\Profile;

return (new Config())
    ->withProfile((new Profile('default'))
        ->withFormatter(new PrettyFormatter(
            timer: false,
            paths: false,
        ))
    )
    ->withProfile((new Profile('hide_skipped'))
        ->withFormatter(new PrettyFormatter(
            printSkippedSteps: false,
            timer: false,
            paths: false,
        ))
    )
;

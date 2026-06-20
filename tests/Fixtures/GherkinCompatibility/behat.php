<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Formatter\PrettyFormatter;
use Behat\Config\GherkinOptions;
use Behat\Config\Profile;
use Behat\Gherkin\GherkinCompatibilityMode;

return (new Config())
    ->withProfile((new Profile('default'))
        ->withFormatter(new PrettyFormatter(
            timer: false,
            paths: false,
        ))
    )->withProfile((new Profile('gherkin-legacy'))
        ->withGherkinOptions((new GherkinOptions())
            ->withCompatibilityMode(GherkinCompatibilityMode::LEGACY)
            ->withCacheDir('/tmp/gc')
        )
    )
;

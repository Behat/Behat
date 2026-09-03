<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Formatter\PrettyFormatter;
use Behat\Config\Formatter\ProgressFormatter;
use Behat\Config\GherkinOptions;
use Behat\Config\Profile;
use Behat\Gherkin\GherkinCompatibilityMode;

return (new Config())
    ->withProfile((new Profile('default'))
        ->withFormatter(new ProgressFormatter())
        ->disableFormatter(PrettyFormatter::NAME)
        ->withGherkinOptions((new GherkinOptions())
            ->withCacheDir('')
            ->withCompatibilityMode(GherkinCompatibilityMode::GHERKIN_32)
        )
    )
    ->withProfile((new Profile('legacy-gherkin'))
        ->withGherkinOptions((new GherkinOptions())
            ->withCacheDir('')
            ->withCompatibilityMode(GherkinCompatibilityMode::LEGACY)
        )
    )
    ;

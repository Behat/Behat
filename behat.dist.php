<?php

use Behat\Config\Config;
use Behat\Config\GherkinOptions;
use Behat\Config\Profile;
use Behat\Config\TesterOptions;
use Behat\Gherkin\GherkinCompatibilityMode;

return (new Config())
    ->withProfile((new Profile('default'))
            ->withGherkinOptions((new GherkinOptions())
                    ->withCompatibilityMode(GherkinCompatibilityMode::GHERKIN_32),
            )
        ->withTesterOptions((new TesterOptions())
            ->withFailOnBehatDeprecations()
        )
    );

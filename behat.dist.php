<?php

use Behat\Config\Config;
use Behat\Config\Filter\TagFilter;
use Behat\Config\GherkinOptions;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Behat\Config\TesterOptions;
use Behat\Gherkin\GherkinCompatibilityMode;

/*
 * Scenarios that specify a gherkin mode in their own config (or specify that they should use Behat's
 * own default) should be tagged '@gherkin-mode:has-explicit'. They will only run in this suite.
 */
$explicitGherkinSuite = (new Suite('explicit-gherkin-mode'))
    ->addContext(FeatureContext::class, ['gherkinCompatibilityMode' => null])
    ->withFilter(new TagFilter('@gherkin-mode:has-explicit'));

/*
 * Scenarios that do not have the `@gherkin-mode:has-explict` tag will run twice - once with the default forced to
 * `gherkin-32`, and once with the default forced to `legacy`. This ensures that Behat's behaviour is the same in both
 * parsing modes (unless we specify otherwise).
 */
$createGherkinModeSuite = fn (string $name, GherkinCompatibilityMode $mode): Suite => (new Suite($name))
    ->addContext(FeatureContext::class, ['gherkinCompatibilityMode' => $mode])
    ->withFilter(new TagFilter('~@gherkin-mode:has-explicit'));

return (new Config())
    ->withProfile(
        (new Profile('default'))
            ->withSuite($explicitGherkinSuite)
            ->withSuite($createGherkinModeSuite('gherkin-32', GherkinCompatibilityMode::GHERKIN_32))
            ->withSuite($createGherkinModeSuite('gherkin-legacy', GherkinCompatibilityMode::LEGACY))
            ->withGherkinOptions((new GherkinOptions())
                // Our outer runner will always be in gherkin-32 mode, regardless of the mode it runs Behat in
                ->withCompatibilityMode(GherkinCompatibilityMode::GHERKIN_32),
            )
            ->withTesterOptions((new TesterOptions())
                ->withFailOnBehatDeprecations(),
            ),
    );

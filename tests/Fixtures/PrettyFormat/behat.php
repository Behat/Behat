<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Filter\TagFilter;
use Behat\Config\GherkinOptions;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Behat\Gherkin\GherkinCompatibilityMode;

$defaultProfile = (new Profile('default'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('FeatureContext')
    )
;

$multilineProfile = (new Profile('multiline'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('FeatureContextMultiline')
    )
;

$emptyProfile = (new Profile('empty'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('FeatureContextEmpty')
    )
;

$backgroundFailingProfile = (new Profile('background_failing'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('FeatureContextBackgroundFailing')
    )
;

$multipleExamplesProfile = (new Profile('multiple_examples'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('FeatureContextMultipleExamples')
    )
;

$lsProfile = (new Profile('ls'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('FeatureContextLs')
    )
;

$parityLegacyProfile = (new Profile('gherkin-parity-legacy'))
    ->withSuite(
        (new Suite('default'))
            ->withContexts('FeatureContext'),
    )
    ->withGherkinOptions(
        (new GherkinOptions())
            ->withFilter(new TagFilter('@gherkin-parity'))
            ->withCompatibilityMode(GherkinCompatibilityMode::LEGACY),
    );

$parityGherkin32Profile = (new Profile('gherkin-parity-gherkin-32', $parityLegacyProfile->toArray()))
    ->withGherkinOptions(
        (new GherkinOptions())
            ->withFilter(new TagFilter('@gherkin-parity'))
            ->withCompatibilityMode(GherkinCompatibilityMode::GHERKIN_32),
    );

return (new Config())
    ->withProfile($defaultProfile)
    ->withProfile($multilineProfile)
    ->withProfile($emptyProfile)
    ->withProfile($backgroundFailingProfile)
    ->withProfile($multipleExamplesProfile)
    ->withProfile($lsProfile)
    ->withProfile($parityLegacyProfile)
    ->withProfile($parityGherkin32Profile)
;

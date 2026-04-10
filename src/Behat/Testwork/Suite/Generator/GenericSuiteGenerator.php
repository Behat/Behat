<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Suite\Generator;

use Behat\Testwork\Suite\GenericSuite;
use Behat\Testwork\Suite\Suite;

/**
 * Generates generic test suites.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class GenericSuiteGenerator implements SuiteGenerator
{
    /**
     * Initializes suite generator.
     */
    public function __construct(
        private readonly array $defaultSettings = [],
    ) {
    }

    public function supportsTypeAndSettings(?string $type, array $settings): bool
    {
        return null === $type;
    }

    public function generateSuite(string $suiteName, array $settings): Suite
    {
        return new GenericSuite($suiteName, $this->mergeDefaultSettings($settings));
    }

    /**
     * Merges provided settings into default ones.
     */
    private function mergeDefaultSettings(array $settings): array
    {
        return array_merge($this->defaultSettings, $settings);
    }
}

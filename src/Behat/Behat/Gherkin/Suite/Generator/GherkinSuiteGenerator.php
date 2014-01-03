<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Gherkin\Suite\Generator;

use Behat\Behat\Gherkin\Suite\GherkinSuite;
use Behat\Testwork\Suite\Generator\SuiteGenerator;
use Behat\Testwork\Suite\Suite;

/**
 * Gherkin suite generator.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class GherkinSuiteGenerator implements SuiteGenerator
{
    /**
     * @var array
     */
    private $defaultSettings = array();

    /**
     * Initializes suite generator.
     *
     * @param array $defaultSettings
     */
    public function __construct(array $defaultSettings = array())
    {
        $this->defaultSettings = $defaultSettings;
    }

    /**
     * Checks if generator support provided suite type and settings.
     *
     * @param string $type
     * @param array  $settings
     *
     * @return Boolean
     */
    public function supportsTypeAndSettings($type, array $settings)
    {
        return null === $type;
    }

    /**
     * Generate suite with provided name, settings and parameters.
     *
     * @param string $suiteName
     * @param array  $settings
     * @param array  $parameters
     *
     * @return Suite
     */
    public function generateSuite($suiteName, array $settings, array $parameters)
    {
        return new GherkinSuite($suiteName, $this->mergeDefaultSettings($settings), $parameters);
    }

    /**
     * Merges provided settings into default ones.
     *
     * @param array $settings
     *
     * @return array
     */
    private function mergeDefaultSettings(array $settings)
    {
        return array_merge($this->defaultSettings, $settings);
    }
}

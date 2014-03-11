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

/**
 * Generates generic test suites.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class GenericSuiteGenerator implements SuiteGenerator
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
     * {@inheritdoc}
     */
    public function supportsTypeAndSettings($type, array $settings)
    {
        return null === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function generateSuite($suiteName, array $settings)
    {
        return new GenericSuite($suiteName, $this->mergeDefaultSettings($settings));
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

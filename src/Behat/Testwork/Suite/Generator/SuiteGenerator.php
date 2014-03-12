<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Suite\Generator;

use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Suite\SuiteRegistry;

/**
 * Generates a suite using provided name, settings and parameters.
 *
 * @see SuiteRegistry
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SuiteGenerator
{
    /**
     * Checks if generator support provided suite type and settings.
     *
     * @param string $type
     * @param array  $settings
     *
     * @return Boolean
     */
    public function supportsTypeAndSettings($type, array $settings);

    /**
     * Generate suite with provided name and settings.
     *
     * @param string $suiteName
     * @param array  $settings
     *
     * @return Suite
     */
    public function generateSuite($suiteName, array $settings);
}

<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Environment\Handler;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\StaticEnvironment;
use Behat\Testwork\Suite\Suite;

/**
 * Static test environment handler.
 *
 * Environment handler based on static calls (no isolation).
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StaticEnvironmentHandler implements EnvironmentHandler
{
    /**
     * Checks if handler supports provided suite.
     *
     * @param Suite $suite
     *
     * @return Boolean
     */
    public function supportsSuite(Suite $suite)
    {
        return true;
    }

    /**
     * Builds environment object based on provided suite.
     *
     * @param Suite $suite
     *
     * @return Environment
     */
    public function buildEnvironment(Suite $suite)
    {
        return new StaticEnvironment($suite);
    }

    /**
     * Checks if handler supports provided environment.
     *
     * @param Environment $environment
     * @param mixed       $testSubject
     *
     * @return Boolean
     */
    public function supportsEnvironmentAndSubject(Environment $environment, $testSubject = null)
    {
        return $environment instanceof StaticEnvironment;
    }

    /**
     * Isolates provided environment.
     *
     * @param Environment $environment
     * @param mixed       $testSubject
     *
     * @return Environment
     */
    public function isolateEnvironment(Environment $environment, $testSubject = null)
    {
        return $environment;
    }
}

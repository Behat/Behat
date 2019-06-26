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
use Behat\Testwork\Environment\EnvironmentManager;
use Behat\Testwork\Suite\Suite;

/**
 * Handles test environment building and isolation.
 *
 * @see EnvironmentManager
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface EnvironmentHandler
{
    /**
     * Checks if handler supports provided suite.
     *
     * @param Suite $suite
     *
     * @return bool
     */
    public function supportsSuite(Suite $suite);

    /**
     * Builds environment object based on provided suite.
     *
     * @param Suite $suite
     *
     * @return Environment
     */
    public function buildEnvironment(Suite $suite);

    /**
     * Checks if handler supports provided environment.
     *
     * @param Environment $environment
     * @param mixed       $testSubject
     *
     * @return bool
     */
    public function supportsEnvironmentAndSubject(Environment $environment, $testSubject = null);

    /**
     * Isolates provided environment.
     *
     * @param Environment $environment
     * @param mixed       $testSubject
     *
     * @return Environment
     */
    public function isolateEnvironment(Environment $environment, $testSubject = null);
}

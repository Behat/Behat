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
use Behat\Testwork\Suite\Suite;

/**
 * Testwork test environment handler interface.
 *
 * Handles test environment building and isolation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface EnvironmentHandler
{
    /**
     * Checks if handler supports provided suite.
     *
     * @param Suite      $suite
     * @param null|mixed $subject
     *
     * @return Boolean
     */
    public function supportsSuiteAndSubject(Suite $suite, $subject = null);

    /**
     * Builds environment object based on provided suite.
     *
     * @param Suite      $suite
     * @param null|mixed $subject
     *
     * @return Environment
     */
    public function buildEnvironment(Suite $suite, $subject = null);

    /**
     * Checks if handler supports provided environment.
     *
     * @param Environment $environment
     *
     * @return Boolean
     */
    public function supportsEnvironment(Environment $environment);

    /**
     * Isolates provided environment.
     *
     * @param Environment $environment
     *
     * @return Environment
     */
    public function isolateEnvironment(Environment $environment);
}

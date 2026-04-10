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
     */
    public function supportsSuite(Suite $suite): bool;

    /**
     * Builds environment object based on provided suite.
     */
    public function buildEnvironment(Suite $suite): Environment;

    /**
     * Checks if handler supports provided environment.
     */
    public function supportsEnvironmentAndSubject(Environment $environment, $testSubject = null): bool;

    /**
     * Isolates provided environment.
     */
    public function isolateEnvironment(Environment $environment, $testSubject = null): Environment;
}

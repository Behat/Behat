<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Environment;

use Behat\Behat\Context\Environment\Handler\ContextEnvironmentHandler;
use Behat\Testwork\Environment\Environment;

/**
 * Represents test environment based on a collection of contexts.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ContextEnvironment extends Environment
{
    /**
     * Checks if environment has any contexts registered.
     *
     * @return Boolean
     */
    public function hasContexts();

    /**
     * Returns list of registered context classes.
     *
     * @return string[]
     */
    public function getContextClasses();

    /**
     * Checks if environment contains context with the specified class name.
     *
     * @param string $class
     *
     * @return Boolean
     */
    public function hasContextClass($class);
}

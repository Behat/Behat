<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Environment;

use Behat\Behat\Context\Context;
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
     * @return bool
     */
    public function hasContexts();

    /**
     * Returns list of registered context classes.
     *
     * @return list<class-string<Context>>
     */
    public function getContextClasses();

    /**
     * Checks if environment contains context with the specified class name.
     *
     * @param class-string<Context> $class
     *
     * @return bool
     */
    public function hasContextClass($class);
}

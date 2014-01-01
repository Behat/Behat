<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Pool;

/**
 * Context pool interface.
 *
 * Represents pool (collection) of context classes.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ContextPool
{
    /**
     * Checks if pool has any contexts registered.
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
     * Checks if pool contains context with the specified class name.
     *
     * @param string $class
     *
     * @return Boolean
     */
    public function hasContextClass($class);
}

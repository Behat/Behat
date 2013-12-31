<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Pool;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Exception\ContextNotFoundException;

/**
 * Initialized context pool.
 *
 * Pool containing instantiated context objects.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class InitializedContextPool implements ContextPool
{
    /**
     * @var Context[]
     */
    private $contexts = array();

    /**
     * Registers context instance in the pool.
     *
     * @param Context $context
     */
    public function registerContext(Context $context)
    {
        $this->contexts[get_class($context)] = $context;
    }

    /**
     * Checks if pool has any contexts registered.
     *
     * @return Boolean
     */
    public function hasContexts()
    {
        return count($this->contexts) > 0;
    }

    /**
     * Returns list of registered context classes.
     *
     * @return string[]
     */
    public function getContextClasses()
    {
        return array_keys($this->contexts);
    }

    /**
     * Checks if pool contains context with specified class name.
     *
     * @param string $class
     *
     * @return Boolean
     */
    public function hasContextClass($class)
    {
        return isset($this->contexts[$class]);
    }

    /**
     * Returns list of registered context instances.
     *
     * @return Context[]
     */
    public function getContexts()
    {
        return array_values($this->contexts);
    }

    /**
     * Returns registered context by its class name.
     *
     * @param string $class
     *
     * @return Context
     *
     * @throws ContextNotFoundException If context is not in the pool
     */
    public function getContext($class)
    {
        if (!$this->hasContextClass($class)) {
            throw new ContextNotFoundException(sprintf(
                '`%s` context is not found in the context pool. Have you registered it?',
                $class
            ), $class);
        }

        return $this->contexts[$class];
    }
}

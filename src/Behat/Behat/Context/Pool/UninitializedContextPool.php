<?php

namespace Behat\Behat\Context\Pool;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use InvalidArgumentException;

/**
 * Uninitialized context pool.
 * Context pool containing context classes.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class UninitializedContextPool implements ContextPoolInterface
{
    /**
     * @var string[]
     */
    private $contextClasses = array();

    /**
     * Registers context class.
     *
     * @param string $contextClass
     *
     * @throws InvalidArgumentException If class does not exist or does not implement ContextInterface
     */
    public function registerContextClass($contextClass)
    {
        if (!class_exists($contextClass)) {
            throw new InvalidArgumentException(sprintf(
                'Context class "%s" not found and can not be used.',
                $contextClass
            ));
        }
        if (!is_subclass_of($contextClass, 'Behat\Behat\Context\ContextInterface')) {
            throw new InvalidArgumentException(sprintf(
                'Every context class must implement ContextInterface, but "%s" does not.',
                $contextClass
            ));
        }

        $this->contextClasses[$contextClass] = true;
    }

    /**
     * Checks if pool has any contexts registered.
     *
     * @return Boolean
     */
    public function hasContexts()
    {
        return count($this->contextClasses) > 0;
    }

    /**
     * Returns list of registered context classes.
     *
     * @return string[]
     */
    public function getContexts()
    {
        return array_keys($this->contextClasses);
    }

    /**
     * Returns list of registered context classes.
     *
     * @return string[]
     */
    public function getContextClasses()
    {
        return $this->getContexts();
    }

    /**
     * Checks if pool contains context with specified class name.
     *
     * @param string $class
     *
     * @return Boolean
     */
    public function hasContext($class)
    {
        return isset($this->contextClasses[$class]);
    }

    /**
     * Returns registered context by its class name.
     *
     * @param string $class
     *
     * @return string
     *
     * @throws InvalidArgumentException If context is not in the pool
     */
    public function getContext($class)
    {
        if (!$this->hasContext($class)) {
            throw new InvalidArgumentException(sprintf(
                'Context "%s" can not be found in a context pool.',
                $class
            ));
        }

        return $class;
    }

    /**
     * Returns first registered context class.
     *
     * @return null|string
     */
    public function getFirstContext()
    {
        if (!$this->hasContexts()) {
            return null;
        }

        $contexts = $this->getContexts();

        return current($contexts);
    }
}

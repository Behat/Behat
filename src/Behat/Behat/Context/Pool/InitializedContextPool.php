<?php

namespace Behat\Behat\Context\Pool;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\ContextInterface;
use InvalidArgumentException;

/**
 * Initialized context pool.
 * Context pool containing context instances.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class InitializedContextPool implements ContextPoolInterface
{
    /**
     * @var ContextInterface[]
     */
    private $contexts = array();

    /**
     * Registers context instance in the pool.
     *
     * @param ContextInterface $context
     */
    public function registerContext(ContextInterface $context)
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
     * Returns list of registered context instances.
     *
     * @return ContextInterface[]
     */
    public function getContexts()
    {
        return $this->contexts;
    }

    /**
     * Returns list of registered context classes.
     *
     * @return string[]
     */
    public function getContextClasses()
    {
        return array_map('get_class', $this->contexts);
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
        return isset($this->contexts[$class]);
    }

    /**
     * Returns registered context by its class name.
     *
     * @param string $class
     *
     * @return ContextInterface
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

        return $this->contexts[$class];
    }

    /**
     * Returns first registered context instance.
     *
     * @return null|ContextInterface
     */
    public function getFirstContext()
    {
        if (!$this->hasContexts()) {
            return null;
        }

        return end($this->contexts);
    }
}

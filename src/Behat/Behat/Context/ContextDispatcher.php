<?php

namespace Behat\Behat\Context;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Context dispatcher.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextDispatcher
{
    private $contextClass;
    private $parameters = array();

    /**
     * Initialize dispatcher.
     *
     * @param array $parameters context parameters
     */
    public function __construct(array $parameters = array())
    {
        $this->parameters = $parameters;
    }

    /**
     * Sets context class name.
     *
     * @param string $className
     *
     * @throws \InvalidArgumentException
     */
    public function setContextClass($className)
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException(sprintf('Context class "%s" not found', $className));
        }

        $contextClassRefl = new \ReflectionClass($className);
        if (!$contextClassRefl->implementsInterface('Behat\Behat\Context\ContextInterface')) {
            throw new \InvalidArgumentException(sprintf(
                'Context class "%s" should implement ContextInterface', $className
            ));
        }

        $this->contextClass = $className;
    }

    /**
     * Returns context class name.
     *
     * @return  string
     */
    public function getContextClass()
    {
        return $this->contextClass;
    }

    /**
     * Returns context parameters.
     *
     * @return  array
     */
    public function getContextParameters()
    {
        return $this->parameters;
    }

    /**
     * Creates new context instance.
     *
     * @return ContextInterface
     *
     * @throws \RuntimeException
     */
    public function createContext()
    {
        if (null === $this->contextClass) {
            throw new \RuntimeException('Specify context class to use for ContextDispatcher');
        }

        return new $this->contextClass($this->getContextParameters());
    }
}

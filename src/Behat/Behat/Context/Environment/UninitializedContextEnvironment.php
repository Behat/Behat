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
use Behat\Behat\Context\Exception\ContextNotFoundException;
use Behat\Behat\Context\Exception\WrongContextClassException;
use Behat\Testwork\Environment\StaticEnvironment;

/**
 * Uninitialized context environment.
 *
 * Environment based on uninitialized context objects (classes).
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class UninitializedContextEnvironment extends StaticEnvironment implements ContextEnvironment
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
     * @throws ContextNotFoundException If class does not exist
     * @throws WrongContextClassException if class does not implement Context interface
     */
    public function registerContextClass($contextClass)
    {
        if (!class_exists($contextClass)) {
            throw new ContextNotFoundException(sprintf(
                '`%s` context class not found and can not be used.',
                $contextClass
            ), $contextClass);
        }

        if (!is_subclass_of($contextClass, 'Behat\Behat\Context\Context')) {
            throw new WrongContextClassException(sprintf(
                'Every context class must implement Behat Context interface, but `%s` does not.',
                $contextClass
            ), $contextClass);
        }

        $this->contextClasses[$contextClass] = true;
    }

    /**
     * Checks if environment has any contexts registered.
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
    public function getContextClasses()
    {
        return array_keys($this->contextClasses);
    }

    /**
     * Checks if environment contains context with specified class name.
     *
     * @param string $class
     *
     * @return Boolean
     */
    public function hasContextClass($class)
    {
        return isset($this->contextClasses[$class]);
    }
}

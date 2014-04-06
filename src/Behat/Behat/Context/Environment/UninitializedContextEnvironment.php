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
 * Context environment based on a list of context classes.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class UninitializedContextEnvironment extends StaticEnvironment implements ContextEnvironment
{
    /**
     * @var array[]
     */
    private $contextClasses = array();

    /**
     * Registers context class.
     *
     * @param string     $contextClass
     * @param null|array $arguments
     *
     * @throws ContextNotFoundException   If class does not exist
     * @throws WrongContextClassException if class does not implement Context interface
     */
    public function registerContextClass($contextClass, array $arguments = null)
    {
        if (!class_exists($contextClass)) {
            throw new ContextNotFoundException(sprintf(
                '`%s` context class not found and can not be used.',
                $contextClass
            ), $contextClass);
        }

        $reflClass = new \ReflectionClass($contextClass);

        if (!$reflClass->implementsInterface('Behat\Behat\Context\Context')) {
            throw new WrongContextClassException(sprintf(
                'Every context class must implement Behat Context interface, but `%s` does not.',
                $contextClass
            ), $contextClass);
        }

        $this->contextClasses[$contextClass] = $arguments ? : array();
    }

    /**
     * {@inheritdoc}
     */
    public function hasContexts()
    {
        return count($this->contextClasses) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getContextClasses()
    {
        return array_keys($this->contextClasses);
    }

    /**
     * {@inheritdoc}
     */
    public function hasContextClass($class)
    {
        return isset($this->contextClasses[$class]);
    }

    /**
     * Returns context classes with their arguments.
     *
     * @return array[]
     */
    public function getContextClassesWithArguments()
    {
        return $this->contextClasses;
    }
}

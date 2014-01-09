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
 * @see    ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class UninitializedContextEnvironment extends StaticEnvironment implements ContextEnvironment
{
    /**
     * @var string[string]
     */
    private $signatures = array();

    /**
     * Registers context class signature.
     *
     * @param string     $classname
     * @param null|array $arguments
     *
     * @throws ContextNotFoundException If class does not exist
     * @throws WrongContextClassException if class does not implement Context interface
     */
    public function registerContextSignature($classname, array $arguments = null)
    {
        if (!class_exists($classname)) {
            throw new ContextNotFoundException(sprintf(
                '`%s` context class not found and can not be used.',
                $classname
            ), $classname);
        }

        if (!is_subclass_of($classname, 'Behat\Behat\Context\Context')) {
            throw new WrongContextClassException(sprintf(
                'Every context class must implement Behat Context interface, but `%s` does not.',
                $classname
            ), $classname);
        }

        $this->signatures[$classname] = $arguments ?: array();
    }

    /**
     * Checks if environment has any contexts registered.
     *
     * @return Boolean
     */
    public function hasContexts()
    {
        return count($this->signatures) > 0;
    }

    /**
     * Returns list of registered context classes.
     *
     * @return string[]
     */
    public function getContextClasses()
    {
        return array_keys($this->signatures);
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
        return isset($this->signatures[$class]);
    }

    /**
     * Returns context signatures (hash of classes and constructor arguments).
     *
     * @return string[string]
     */
    public function getContextSignatures()
    {
        return $this->signatures;
    }
}

<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Environment\Handler;

use Behat\Behat\Context\Argument\ArgumentResolver;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\ContextClass\ClassResolver;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Context\Environment\UninitializedContextEnvironment;
use Behat\Behat\Context\Initializer\ContextInitializer;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\Handler\EnvironmentHandler;
use Behat\Testwork\Suite\Suite;

/**
 * Context-based environment handler.
 *
 * Handles build and initialisation of context-based environments using registered context initializers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextEnvironmentHandler implements EnvironmentHandler
{
    /**
     * @var ClassResolver[]
     */
    private $classResolvers = array();
    /**
     * @var ArgumentResolver[]
     */
    private $argumentResolvers = array();
    /**
     * @var ContextInitializer[]
     */
    private $contextInitializers = array();
    /**
     * @var mixed[][string]
     */
    private $arguments = array();

    /**
     * Registers context class resolver.
     *
     * @param ClassResolver $resolver
     */
    public function registerClassResolver(ClassResolver $resolver)
    {
        $this->classResolvers[] = $resolver;
    }

    /**
     * Registers context argument resolver.
     *
     * @param ArgumentResolver $resolver
     */
    public function registerArgumentResolver(ArgumentResolver $resolver)
    {
        $this->argumentResolvers[] = $resolver;
    }

    /**
     * Registers context initializer.
     *
     * @param ContextInitializer $initializer
     */
    public function registerContextInitializer(ContextInitializer $initializer)
    {
        $this->contextInitializers[] = $initializer;
    }

    /**
     * Sets specific context class arguments.
     *
     * @param string  $class
     * @param mixed[] $arguments
     */
    public function setContextArguments($class, array $arguments)
    {
        $class = $this->resolveClass($class);
        $this->arguments[$class] = $arguments;
    }

    /**
     * Checks if handler supports provided suite.
     *
     * @param Suite $suite
     *
     * @return Boolean
     */
    public function supportsSuite(Suite $suite)
    {
        return $suite->hasSetting('contexts') && is_array($suite->getSetting('contexts'));
    }

    /**
     * Builds environment object based on provided suite.
     *
     * @param Suite $suite
     *
     * @return UninitializedContextEnvironment
     */
    public function buildEnvironment(Suite $suite)
    {
        $environment = new UninitializedContextEnvironment($suite);
        foreach ($suite->getSetting('contexts') as $class) {
            $class = $this->resolveClass($class);
            $environment->registerContextClass($class);
        }

        return $environment;
    }

    /**
     * Checks if handler supports provided environment.
     *
     * @param Environment $environment
     * @param mixed       $testSubject
     *
     * @return Boolean
     */
    public function supportsEnvironmentAndSubject(Environment $environment, $testSubject = null)
    {
        return $environment instanceof UninitializedContextEnvironment;
    }

    /**
     * Isolates provided environment.
     *
     * @param UninitializedContextEnvironment $uninitializedEnvironment
     * @param mixed                           $testSubject
     *
     * @return InitializedContextEnvironment
     */
    public function isolateEnvironment(Environment $uninitializedEnvironment, $testSubject = null)
    {
        $environment = new InitializedContextEnvironment($uninitializedEnvironment->getSuite());
        foreach ($uninitializedEnvironment->getContextClasses() as $class) {
            $arguments = $this->resolveClassArguments($class);
            $context = $this->initializeContext($class, $arguments);
            $environment->registerContext($context);
        }

        return $environment;
    }

    /**
     * Resolves class using registered class resolvers.
     *
     * @param string $class
     *
     * @return string
     */
    final protected function resolveClass($class)
    {
        foreach ($this->classResolvers as $resolver) {
            if ($resolver->supportsClass($class)) {
                return $resolver->resolveClass($class);
            }
        }

        return $class;
    }

    /**
     * Resolves arguments for a specific class using registered argument resolvers.
     *
     * @param string $class
     *
     * @return mixed[]
     */
    final protected function resolveClassArguments($class)
    {
        $arguments = isset($this->arguments[$class]) ? $this->arguments[$class] : array();

        foreach ($this->argumentResolvers as $resolver) {
            $arguments = $resolver->resolveArguments($class, $arguments);
        }

        return $arguments;
    }

    /**
     * Initializes context class and returns new context instance.
     *
     * @param string $class
     * @param array  $constructorArguments
     *
     * @return Context
     */
    final protected function initializeContext($class, array $constructorArguments)
    {
        $context = new $class($constructorArguments);

        foreach ($this->contextInitializers as $initializer) {
            $initializer->initializeContext($context);
        }

        return $context;
    }
}

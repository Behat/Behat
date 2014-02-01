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
        foreach ($this->getNormalizedContextSettings($suite) as $context) {
            list($class, $arguments) = $context;

            $class = $this->resolveClass($class);
            $environment->registerContextClass($class, $arguments);
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
        foreach ($uninitializedEnvironment->getContextClassesWithArguments() as $class => $arguments) {
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
     * Initializes context class and returns new context instance.
     *
     * @param string $class
     * @param array  $arguments
     *
     * @return Context
     */
    final protected function initializeContext($class, array $arguments)
    {
        $arguments = $this->resolveClassArguments($class, $arguments);
        $context = new $class($arguments);

        foreach ($this->contextInitializers as $initializer) {
            $initializer->initializeContext($context);
        }

        return $context;
    }

    /**
     * Resolves arguments for a specific class using registered argument resolvers.
     *
     * @param string $class
     * @param array  $arguments
     *
     * @return mixed[]
     */
    final protected function resolveClassArguments($class, array $arguments)
    {
        foreach ($this->argumentResolvers as $resolver) {
            $arguments = $resolver->resolveArguments($class, $arguments);
        }

        return $arguments;
    }

    /**
     * Returns normalized suite context settings.
     *
     * @param Suite $suite
     *
     * @return array
     */
    private function getNormalizedContextSettings(Suite $suite)
    {
        return array_map(
            function ($context) {
                $class = $context;
                $arguments = array();

                if (is_array($context)) {
                    $class = current(array_keys($context));
                    $arguments = $context[$class];
                }

                return array($class, $arguments);
            },
            $suite->getSetting('contexts')
        );
    }
}

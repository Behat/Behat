<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Environment\Handler;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Context\Environment\UninitializedContextEnvironment;
use Behat\Behat\Context\Initializer\ContextInitializer;
use Behat\Behat\Context\Pool\InitializedContextPool;
use Behat\Behat\Context\Pool\UninitializedContextPool;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\Handler\EnvironmentHandler;
use Behat\Testwork\Suite\Suite;

/**
 * Context-based environment handler.
 *
 * Handles build and initialisation of ContextPool-based environments using registered context initializers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextEnvironmentHandler implements EnvironmentHandler
{
    /**
     * @var ContextInitializer[]
     */
    private $initializers = array();

    /**
     * Registers context initializer.
     *
     * @param ContextInitializer $initializer
     */
    public function registerContextInitializer(ContextInitializer $initializer)
    {
        $this->initializers[] = $initializer;
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
        return ($suite->hasSetting('contexts') && is_array($suite->getSetting('contexts')))
            || ($suite->hasSetting('context') && null !== $suite->getSetting('context'));
    }

    /**
     * Builds environment object based on provided suite.
     *
     * @param Suite $suite
     *
     * @return Environment
     */
    public function buildEnvironment(Suite $suite)
    {
        $constructorArguments = $suite->hasSetting('parameters') ? (array) $suite->getSetting('parameters') : array();
        $contextPool = new UninitializedContextPool($constructorArguments);

        foreach ($this->getContextClasses($suite) as $class) {
            $contextPool->registerContextClass($class);
        }

        return new UninitializedContextEnvironment($suite->getName(), $contextPool);
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
     * @param UninitializedContextEnvironment $environment
     * @param mixed                           $testSubject
     *
     * @return InitializedContextEnvironment
     */
    public function isolateEnvironment(Environment $environment, $testSubject = null)
    {
        $uninitializedPool = $environment->getContextPool();
        $initializedPool = new InitializedContextPool();
        $constructorArguments = $uninitializedPool->getConstructorArguments();

        foreach ($uninitializedPool->getContextClasses() as $class) {
            $context = $this->initializeContext($class, $constructorArguments);
            $initializedPool->registerContext($context);
        }

        return new InitializedContextEnvironment($environment->getSuiteName(), $initializedPool);
    }

    /**
     * Initializes context class and returns new context instance.
     *
     * @param string $classname
     * @param array  $constructorArguments
     *
     * @return Context
     */
    final protected function initializeContext($classname, array $constructorArguments)
    {
        $context = new $classname($constructorArguments);

        foreach ($this->initializers as $initializer) {
            $initializer->initializeContext($context);
        }

        return $context;
    }

    /**
     * Returns array of context classes from the suite.
     *
     * @param Suite $suite
     *
     * @return string[]
     */
    private function getContextClasses(Suite $suite)
    {
        if ($suite->hasSetting('context') && null !== $suite->getSetting('context')) {
            return array($suite->getSetting('context'));
        }

        return $suite->getSetting('contexts');
    }
}

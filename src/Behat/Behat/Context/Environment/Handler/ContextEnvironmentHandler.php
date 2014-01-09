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

        foreach ($suite->getSetting('contexts') as $class => $arguments) {
            $environment->registerContextSignature($class, $arguments);
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

        foreach ($uninitializedEnvironment->getContextSignatures() as $class => $arguments) {
            $context = $this->initializeContext($class, $arguments);
            $environment->registerContext($context);
        }

        return $environment;
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
}

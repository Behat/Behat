<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Environment\Handler;

use Behat\Behat\Context\Argument\SuiteScopedResolverFactory;
use Behat\Behat\Context\Argument\NullFactory;
use Behat\Behat\Context\ContextClass\ClassResolver;
use Behat\Behat\Context\ContextFactory;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Context\Environment\UninitializedContextEnvironment;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Environment\Exception\EnvironmentIsolationException;
use Behat\Testwork\Environment\Handler\EnvironmentHandler;
use Behat\Testwork\Suite\Exception\SuiteConfigurationException;
use Behat\Testwork\Suite\Suite;

/**
 * Handles build and initialisation of the context-based environments.
 *
 * @see ContextFactory
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ContextEnvironmentHandler implements EnvironmentHandler
{
    /**
     * @var ContextFactory
     */
    private $contextFactory;
    /**
     * @var SuiteScopedResolverFactory
     */
    private $resolverFactory;
    /**
     * @var ClassResolver[]
     */
    private $classResolvers = array();

    /**
     * Initializes handler.
     *
     * @param ContextFactory             $factory
     * @param SuiteScopedResolverFactory $resolverFactory
     */
    public function __construct(ContextFactory $factory, SuiteScopedResolverFactory $resolverFactory = null)
    {
        $this->contextFactory = $factory;
        $this->resolverFactory = $resolverFactory ?: new NullFactory();
    }

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
     * {@inheritdoc}
     */
    public function supportsSuite(Suite $suite)
    {
        return $suite->hasSetting('contexts');
    }

    /**
     * {@inheritdoc}
     */
    public function buildEnvironment(Suite $suite)
    {
        $environment = new UninitializedContextEnvironment($suite);
        foreach ($this->getNormalizedContextSettings($suite) as $context) {
            $environment->registerContextClass($this->resolveClass($context[0]), $context[1]);
        }

        return $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEnvironmentAndSubject(Environment $environment, $testSubject = null)
    {
        return $environment instanceof UninitializedContextEnvironment;
    }

    /**
     * {@inheritdoc}
     */
    public function isolateEnvironment(Environment $uninitializedEnvironment, $testSubject = null)
    {
        if (!$uninitializedEnvironment instanceof UninitializedContextEnvironment) {
            throw new EnvironmentIsolationException(sprintf(
                'ContextEnvironmentHandler does not support isolation of `%s` environment.',
                get_class($uninitializedEnvironment)
            ), $uninitializedEnvironment);
        }

        $environment = new InitializedContextEnvironment($uninitializedEnvironment->getSuite());
        $resolvers = $this->resolverFactory->generateArgumentResolvers($uninitializedEnvironment->getSuite());

        foreach ($uninitializedEnvironment->getContextClassesWithArguments() as $class => $arguments) {
            $context = $this->contextFactory->createContext($class, $arguments, $resolvers);
            $environment->registerContext($context);
        }

        return $environment;
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
            $this->getSuiteContexts($suite)
        );
    }

    /**
     * Returns array of context classes configured for the provided suite.
     *
     * @param Suite $suite
     *
     * @return string[]
     *
     * @throws SuiteConfigurationException If `contexts` setting is not an array
     */
    private function getSuiteContexts(Suite $suite)
    {
        if (!is_array($suite->getSetting('contexts'))) {
            throw new SuiteConfigurationException(
                sprintf('`contexts` setting of the "%s" suite is expected to be an array, %s given.',
                    $suite->getName(),
                    gettype($suite->getSetting('contexts'))
                ),
                $suite->getName()
            );
        }

        return $suite->getSetting('contexts');
    }

    /**
     * Resolves class using registered class resolvers.
     *
     * @param string $class
     *
     * @return string
     */
    private function resolveClass($class)
    {
        foreach ($this->classResolvers as $resolver) {
            if ($resolver->supportsClass($class)) {
                return $resolver->resolveClass($class);
            }
        }

        return $class;
    }
}

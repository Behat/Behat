<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Environment\Handler;

use Behat\Behat\Context\Argument\ArgumentResolverFactory;
use Behat\Behat\Context\Argument\NullFactory;
use Behat\Behat\Context\Argument\SuiteScopedResolverFactory;
use Behat\Behat\Context\Argument\SuiteScopedResolverFactoryAdapter;
use Behat\Behat\Context\Context;
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
     * @var ArgumentResolverFactory
     */
    private $resolverFactory;
    /**
     * @var ClassResolver[]
     */
    private $classResolvers = [];

    /**
     * Initializes handler.
     *
     * @param ArgumentResolverFactory|SuiteScopedResolverFactory $resolverFactory
     */
    public function __construct(
        private readonly ContextFactory $contextFactory,
        $resolverFactory = null,
    ) {
        if ($resolverFactory && !$resolverFactory instanceof ArgumentResolverFactory) {
            $resolverFactory = new SuiteScopedResolverFactoryAdapter($resolverFactory);
        }

        $this->resolverFactory = $resolverFactory ?: new NullFactory();
    }

    /**
     * Registers context class resolver.
     */
    public function registerClassResolver(ClassResolver $resolver): void
    {
        $this->classResolvers[] = $resolver;
    }

    public function supportsSuite(Suite $suite)
    {
        return $suite->hasSetting('contexts');
    }

    public function buildEnvironment(Suite $suite): Environment
    {
        $environment = new UninitializedContextEnvironment($suite);
        foreach ($this->getNormalizedContextSettings($suite) as $context) {
            $environment->registerContextClass($this->resolveClass($context[0]), $context[1]);
        }

        return $environment;
    }

    public function supportsEnvironmentAndSubject(Environment $environment, $testSubject = null): bool
    {
        return $environment instanceof UninitializedContextEnvironment;
    }

    public function isolateEnvironment(Environment $environment, $testSubject = null): InitializedContextEnvironment
    {
        if (!$environment instanceof UninitializedContextEnvironment) {
            throw new EnvironmentIsolationException(sprintf(
                'ContextEnvironmentHandler does not support isolation of `%s` environment.',
                $environment::class
            ), $environment);
        }

        $initialisedEnvironment = new InitializedContextEnvironment($environment->getSuite());
        $resolvers = $this->resolverFactory->createArgumentResolvers($initialisedEnvironment);

        foreach ($environment->getContextClassesWithArguments() as $class => $arguments) {
            $context = $this->contextFactory->createContext($class, $arguments, $resolvers);
            $initialisedEnvironment->registerContext($context);
        }

        return $initialisedEnvironment;
    }

    /**
     * Returns normalized suite context settings.
     *
     * @return array
     */
    private function getNormalizedContextSettings(Suite $suite)
    {
        return array_map(
            function ($context) {
                $class = $context;
                $arguments = [];

                if (is_array($context)) {
                    $class = current(array_keys($context));
                    $arguments = $context[$class];
                }

                return [$class, $arguments];
            },
            $this->getSuiteContexts($suite)
        );
    }

    /**
     * Returns array of context classes configured for the provided suite.
     *
     * @return array<class-string<Context>|array<class-string<Context>,array>>
     *
     * @throws SuiteConfigurationException If `contexts` setting is not an array
     */
    private function getSuiteContexts(Suite $suite)
    {
        if (!is_array($suite->getSetting('contexts'))) {
            throw new SuiteConfigurationException(
                sprintf(
                    '`contexts` setting of the "%s" suite is expected to be an array, %s given.',
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

<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Environment;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\Handler\ContextEnvironmentHandler;
use Behat\Behat\Context\Exception\ContextNotFoundException;
use Behat\Behat\HelperContainer\Environment\ServiceContainerEnvironment;
use Behat\Testwork\Call\Callee;
use Behat\Testwork\Suite\Suite;
use Psr\Container\ContainerInterface;

/**
 * Context environment based on a list of instantiated context objects.
 *
 * @see ContextEnvironmentHandler
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class InitializedContextEnvironment implements ContextEnvironment, ServiceContainerEnvironment
{
    private ?ContainerInterface $serviceContainer = null;

    /**
     * @var array<class-string<Context>, Context>
     *
     * TODO use a class-string-map type to have an accurate type once https://github.com/phpstan/phpstan/issues/9521 is implemented
     */
    private $contexts = [];

    /**
     * Initializes environment.
     */
    public function __construct(
        private readonly Suite $suite,
    ) {
    }

    /**
     * Registers context instance in the environment.
     */
    public function registerContext(Context $context): void
    {
        $this->contexts[$context::class] = $context;
    }

    public function setServiceContainer(?ContainerInterface $container = null): void
    {
        $this->serviceContainer = $container;
    }

    public function getSuite(): Suite
    {
        return $this->suite;
    }

    public function hasContexts(): bool
    {
        return count($this->contexts) > 0;
    }

    public function getContextClasses()
    {
        return array_keys($this->contexts);
    }

    public function hasContextClass($class): bool
    {
        return isset($this->contexts[$class]);
    }

    /**
     * Returns list of registered context instances.
     *
     * @return list<Context>
     */
    public function getContexts()
    {
        return array_values($this->contexts);
    }

    /**
     * Returns registered context by its class name.
     *
     * @template T of Context
     *
     * @param class-string<T> $class
     *
     * @return T
     *
     * @throws ContextNotFoundException If context is not in the environment
     */
    public function getContext($class)
    {
        if (!$this->hasContextClass($class)) {
            throw new ContextNotFoundException(sprintf(
                '`%s` context is not found in the suite environment. Have you registered it?',
                $class
            ), $class);
        }

        return $this->contexts[$class];
    }

    public function getServiceContainer(): ?ContainerInterface
    {
        return $this->serviceContainer;
    }

    public function bindCallee(Callee $callee)
    {
        $callable = $callee->getCallable();

        if ($callee->isAnInstanceMethod() && is_array($callable)) {
            return [$this->getContext($callable[0]), $callable[1]];
        }

        return $callable;
    }
}

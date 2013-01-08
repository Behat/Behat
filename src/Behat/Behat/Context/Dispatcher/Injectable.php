<?php

namespace Behat\Behat\Context\Dispatcher;

use Behat\Behat\Context\ContextInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Dispatcher fetching the context from the service container. It's up to
 * the service container to either create a new instance or reuse an existing
 * one.
 */
class Injectable extends AbstractDispatcher
{
    private $container;
    private $contextId;
    private $className;

    /**
     * Constructor
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $serviceContainer
     * @param string $contextId
     * @param string $className
     */
    public function __construct(ContainerInterface $serviceContainer, $contextId, $className)
    {
        $this->container = $serviceContainer;
        $this->contextId = $contextId;
        $this->className = $className;
    }

    /**
     * Creates new context instance.
     *
     * @return \Behat\Behat\Context\ContextInterface
     *
     * @throws \RuntimeException
     */
    public function createContext()
    {
        try {
            $context = $this->container->get($this->contextId);
        } catch (\ReflectionException $e) {
            throw new \RuntimeException(sprintf(
                'Context class "%s" not found and can not be instantiated.', $this->className
            ));
        }

        if (!$context instanceof ContextInterface) {
            throw new \RuntimeException(sprintf(
                'Context class "%s" must implement ContextInterface', $this->className
            ));
        }

        $this->initializeContext($context);
        return $context;
    }

    /**
     * Returns context classname.
     *
     * @throws \RuntimeException If no class can be found or class can not be created
     * @return string
     */
    public function getContextClass()
    {
        return $this->className;
    }
}

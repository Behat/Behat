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

    /**
     * Constructor
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $serviceContainer
     * @param string $contextId
     */
    public function __construct(ContainerInterface $serviceContainer, $contextId)
    {
        $this->container = $serviceContainer;
        $this->contextId = $contextId;
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
        if (!$this->container->has($this->contextId)) {
            throw new \RuntimeException(sprintf(
                'Context service "%s" not found.', $this->contextId
            ));
        }

        $context = $this->container->get($this->contextId);
        if (!$context instanceof ContextInterface) {
            throw new \RuntimeException(sprintf(
                'Context service "%s" must implement ContextInterface', $this->contextId
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
        if (!$this->container->has($this->contextId)) {
            throw new \RuntimeException(sprintf(
                'Context service "%s" not found.', $this->contextId
            ));
        }

        return get_class($this->container->get($this->contextId));
    }
}

<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\ServiceContainer;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Testwork event dispatcher extension.
 *
 * Provides event dispatching service.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class EventDispatcherExtension implements Extension
{
    /*
     * Available services
     */
    const DISPATCHER_ID = 'event_dispatcher';

    /*
     * Available extension points
     */
    const SUBSCRIBER_TAG = 'event_dispatcher.subscriber';

    /**
     * @var ServiceProcessor
     */
    private $processor;

    /**
     * Initializes extension.
     *
     * @param null|ServiceProcessor $processor
     */
    public function __construct(ServiceProcessor $processor = null)
    {
        $this->processor = $processor ? : new ServiceProcessor();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return 'events';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadEventDispatcher($container);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->processSubscribers($container);
    }

    /**
     * Loads event dispatcher.
     *
     * @param ContainerBuilder $container
     */
    protected function loadEventDispatcher(ContainerBuilder $container)
    {
        $definition = new Definition('Symfony\Component\EventDispatcher\EventDispatcher');
        $container->setDefinition(self::DISPATCHER_ID, $definition);
    }

    /**
     * Registers all available event subscribers.
     *
     * @param ContainerBuilder $container
     */
    protected function processSubscribers(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::SUBSCRIBER_TAG);
        $definition = $container->getDefinition(self::DISPATCHER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('addSubscriber', array($reference));
        }
    }
}

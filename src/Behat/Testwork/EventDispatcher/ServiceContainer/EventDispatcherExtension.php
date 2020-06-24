<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\ServiceContainer;

use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Behat\Testwork\Tester\ServiceContainer\TesterExtension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Provides event dispatching service.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class EventDispatcherExtension implements Extension
{
    /*
     * Available services
     */
    public const DISPATCHER_ID = 'event_dispatcher';

    /*
     * Available extension points
     */
    public const SUBSCRIBER_TAG = 'event_dispatcher.subscriber';

    /**
     * @var ServiceProcessor
     */
    protected $processor;

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
        $this->loadSigintController($container);
        $this->loadEventDispatcher($container);
        $this->loadEventDispatchingExercise($container);
        $this->loadEventDispatchingSuiteTester($container);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->processSubscribers($container);
    }

    /**
     * Loads sigint controller
     *
     * @param ContainerBuilder $container
     */
    protected function loadSigintController(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\EventDispatcher\Cli\SigintController', array(
            new Reference(EventDispatcherExtension::DISPATCHER_ID)
        ));
        $definition->addTag(CliExtension::CONTROLLER_TAG, array('priority' => 9999));
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.sigint', $definition);
    }

    /**
     * Loads event dispatcher.
     *
     * @param ContainerBuilder $container
     */
    protected function loadEventDispatcher(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\EventDispatcher\TestworkEventDispatcher');
        $container->setDefinition(self::DISPATCHER_ID, $definition);
    }

    /**
     * Loads event-dispatching exercise.
     *
     * @param ContainerBuilder $container
     */
    protected function loadEventDispatchingExercise(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\EventDispatcher\Tester\EventDispatchingExercise', array(
            new Reference(TesterExtension::EXERCISE_ID),
            new Reference(self::DISPATCHER_ID)
        ));
        $definition->addTag(TesterExtension::EXERCISE_WRAPPER_TAG);
        $container->setDefinition(TesterExtension::EXERCISE_WRAPPER_TAG . '.event_dispatching', $definition);
    }

    /**
     * Loads event-dispatching suite tester.
     *
     * @param ContainerBuilder $container
     */
    protected function loadEventDispatchingSuiteTester(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\EventDispatcher\Tester\EventDispatchingSuiteTester', array(
            new Reference(TesterExtension::SUITE_TESTER_ID),
            new Reference(self::DISPATCHER_ID)
        ));
        $definition->addTag(TesterExtension::SUITE_TESTER_WRAPPER_TAG, array('priority' => -9999));
        $container->setDefinition(TesterExtension::SUITE_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);
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

<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\ServiceContainer;

use Behat\Testwork\Call\ServiceContainer\CallExtension;
use Behat\Testwork\Environment\ServiceContainer\EnvironmentExtension;
use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\Tester\ServiceContainer\TesterExtension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Testwork hook extension.
 *
 * Provides test hooking services for testwork.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HookExtension implements Extension
{
    /*
     * Available services
     */
    const DISPATCHER_ID = 'hook.dispatcher';
    const REPOSITORY_ID = 'hook.repository';
    const EVENT_SUBSCRIBER = 'hook.event_subscriber';

    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return 'hooks';
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
        $this->loadDispatcher($container);
        $this->loadRepository($container);
        $this->loadHookedEventsSubscriber($container);
        $this->loadHookedResultInterpretation($container);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }

    /**
     * Loads hook dispatcher.
     *
     * @param ContainerBuilder $container
     */
    protected function loadDispatcher(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Hook\HookDispatcher', array(
            new Reference(self::REPOSITORY_ID),
            new Reference(CallExtension::CALL_CENTER_ID)
        ));
        $container->setDefinition(self::DISPATCHER_ID, $definition);
    }

    /**
     * Loads hook repository.
     *
     * @param ContainerBuilder $container
     */
    protected function loadRepository(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Hook\HookRepository', array(
            new Reference(EnvironmentExtension::MANAGER_ID)
        ));
        $container->setDefinition(self::REPOSITORY_ID, $definition);
    }

    /**
     * Loads hooked events subscriber.
     *
     * @param ContainerBuilder $container
     */
    protected function loadHookedEventsSubscriber(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Hook\EventDispatcher\HookedEventsSubscriber', array(
            new Reference(self::DISPATCHER_ID),
            new Reference(EventDispatcherExtension::DISPATCHER_ID)
        ));
        $definition->addTag(EventDispatcherExtension::SUBSCRIBER_TAG);
        $container->setDefinition(self::EVENT_SUBSCRIBER, $definition);
    }

    /**
     * Loads hooked result interpreter.
     *
     * @param ContainerBuilder $container
     */
    protected function loadHookedResultInterpretation($container)
    {
        $definition = new Definition('Behat\Testwork\Hook\Output\Result\Interpretation\HookedResultInterpretation');
        $definition->addTag(EventDispatcherExtension::SUBSCRIBER_TAG);
        $definition->addTag(TesterExtension::RESULT_INTERPRETATION_TAG);
        $container->setDefinition(TesterExtension::RESULT_INTERPRETATION_TAG . '.hooked', $definition);
    }
}

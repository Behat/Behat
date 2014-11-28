<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\ServiceContainer;

use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\Tester\ServiceContainer\TesterExtension;
use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension as BaseExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Extends Testwork EventDispatcherExtension with additional event-dispatching testers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class EventDispatcherExtension extends BaseExtension
{
    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        parent::load($container, $config);

        $this->loadStopOnFailureController($container);
    }

    /**
     * Loads stop on failure controller.
     *
     * @param ContainerBuilder $container
     */
    protected function loadStopOnFailureController(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\EventDispatcher\Cli\StopOnFailureController', array(
            new Reference(EventDispatcherExtension::DISPATCHER_ID)
        ));
        $definition->addTag(CliExtension::CONTROLLER_TAG, array('priority' => 100));
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.stop_on_failure', $definition);
    }

    /**
     * Loads event factory.
     *
     * @param ContainerBuilder $container
     */
    protected function loadEventFactory(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\EventDispatcher\Event\GherkinEventFactory', array(
            new Definition('Behat\Testwork\EventDispatcher\Event\ExerciseEventFactory')
        ));
        $container->setDefinition(self::EVENT_FACTORY_ID, $definition);
    }

    /**
     * Loads event-dispatching tester.
     *
     * @param ContainerBuilder $container
     */
    protected function loadEventDispatchingTesters(ContainerBuilder $container)
    {
        parent::loadEventDispatchingTesters($container);

        $definition = new Definition('Behat\Testwork\EventDispatcher\Tester\EventDispatchingTester', array(
            new Reference(TesterExtension::BACKGROUND_TESTER_ID),
            new Reference(self::EVENT_FACTORY_ID),
            new Reference(self::DISPATCHER_ID)
        ));
        $definition->addTag(TesterExtension::BACKGROUND_TESTER_WRAPPER_TAG, array('priority' => -9999));
        $container->setDefinition(TesterExtension::BACKGROUND_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);

        $definition = new Definition('Behat\Testwork\EventDispatcher\Tester\EventDispatchingTester', array(
            new Reference(TesterExtension::BACKGROUND_TESTER_ID),
            new Reference(self::EVENT_FACTORY_ID),
            new Reference(self::DISPATCHER_ID)
        ));
        $definition->addTag(TesterExtension::SPECIFICATION_TESTER_WRAPPER_TAG, array('priority' => -9999));
        $container->setDefinition(TesterExtension::SPECIFICATION_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);

        $definition = new Definition('Behat\Testwork\EventDispatcher\Tester\EventDispatchingTester', array(
            new Reference(TesterExtension::BACKGROUND_TESTER_ID),
            new Reference(self::EVENT_FACTORY_ID),
            new Reference(self::DISPATCHER_ID)
        ));
        $definition->addTag(TesterExtension::OUTLINE_TESTER_WRAPPER_TAG, array('priority' => -9999));
        $container->setDefinition(TesterExtension::OUTLINE_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);

        $definition = new Definition('Behat\Testwork\EventDispatcher\Tester\EventDispatchingTester', array(
            new Reference(TesterExtension::BACKGROUND_TESTER_ID),
            new Reference(self::EVENT_FACTORY_ID),
            new Reference(self::DISPATCHER_ID)
        ));
        $definition->addTag(TesterExtension::SCENARIO_TESTER_WRAPPER_TAG, array('priority' => -9999));
        $container->setDefinition(TesterExtension::SCENARIO_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);

        $definition = new Definition('Behat\Testwork\EventDispatcher\Tester\EventDispatchingTester', array(
            new Reference(TesterExtension::BACKGROUND_TESTER_ID),
            new Reference(self::EVENT_FACTORY_ID),
            new Reference(self::DISPATCHER_ID)
        ));
        $definition->addTag(TesterExtension::EXAMPLE_TESTER_WRAPPER_TAG, array('priority' => -9999));
        $container->setDefinition(TesterExtension::EXAMPLE_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);

        $definition = new Definition('Behat\Testwork\EventDispatcher\Tester\EventDispatchingTester', array(
            new Reference(TesterExtension::BACKGROUND_TESTER_ID),
            new Reference(self::EVENT_FACTORY_ID),
            new Reference(self::DISPATCHER_ID)
        ));
        $definition->addTag(TesterExtension::STEP_TESTER_WRAPPER_TAG, array('priority' => -9999));
        $container->setDefinition(TesterExtension::STEP_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);
    }
}

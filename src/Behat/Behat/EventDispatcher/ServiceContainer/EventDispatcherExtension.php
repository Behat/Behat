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
        $this->loadEventDispatchingBackgroundTester($container);
        $this->loadEventDispatchingFeatureTester($container);
        $this->loadEventDispatchingOutlineTester($container);
        $this->loadEventDispatchingScenarioTester($container);
        $this->loadEventDispatchingExampleTester($container);
        $this->loadEventDispatchingStepTester($container);
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
     * Loads event-dispatching background tester.
     *
     * @param ContainerBuilder $container
     */
    protected function loadEventDispatchingBackgroundTester(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\EventDispatcher\Tester\EventDispatchingBackgroundTester', array(
            new Reference(TesterExtension::BACKGROUND_TESTER_ID),
            new Reference(self::DISPATCHER_ID)
        ));
        $definition->addTag(TesterExtension::BACKGROUND_TESTER_WRAPPER_TAG, array('priority' => -9999));
        $container->setDefinition(TesterExtension::BACKGROUND_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);
    }

    /**
     * Loads event-dispatching feature tester.
     *
     * @param ContainerBuilder $container
     */
    protected function loadEventDispatchingFeatureTester(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\EventDispatcher\Tester\EventDispatchingFeatureTester', array(
            new Reference(TesterExtension::SPECIFICATION_TESTER_ID),
            new Reference(self::DISPATCHER_ID)
        ));
        $definition->addTag(TesterExtension::SPECIFICATION_TESTER_WRAPPER_TAG, array('priority' => -9999));
        $container->setDefinition(TesterExtension::SPECIFICATION_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);
    }

    /**
     * Loads event-dispatching outline tester.
     *
     * @param ContainerBuilder $container
     */
    protected function loadEventDispatchingOutlineTester(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\EventDispatcher\Tester\EventDispatchingOutlineTester', array(
            new Reference(TesterExtension::OUTLINE_TESTER_ID),
            new Reference(self::DISPATCHER_ID)
        ));
        $definition->addTag(TesterExtension::OUTLINE_TESTER_WRAPPER_TAG, array('priority' => -9999));
        $container->setDefinition(TesterExtension::OUTLINE_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);
    }

    /**
     * Loads event-dispatching scenario tester.
     *
     * @param ContainerBuilder $container
     */
    protected function loadEventDispatchingScenarioTester(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\EventDispatcher\Tester\EventDispatchingScenarioTester', array(
            new Reference(TesterExtension::SCENARIO_TESTER_ID),
            new Reference(self::DISPATCHER_ID),
            ScenarioTested::BEFORE,
            ScenarioTested::AFTER_SETUP,
            ScenarioTested::BEFORE_TEARDOWN,
            ScenarioTested::AFTER
        ));
        $definition->addTag(TesterExtension::SCENARIO_TESTER_WRAPPER_TAG, array('priority' => -9999));
        $container->setDefinition(TesterExtension::SCENARIO_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);
    }

    /**
     * Loads event-dispatching example tester.
     *
     * @param ContainerBuilder $container
     */
    protected function loadEventDispatchingExampleTester(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\EventDispatcher\Tester\EventDispatchingScenarioTester', array(
            new Reference(TesterExtension::EXAMPLE_TESTER_ID),
            new Reference(self::DISPATCHER_ID),
            ExampleTested::BEFORE,
            ExampleTested::AFTER_SETUP,
            ExampleTested::BEFORE_TEARDOWN,
            ExampleTested::AFTER
        ));
        $definition->addTag(TesterExtension::EXAMPLE_TESTER_WRAPPER_TAG, array('priority' => -9999));
        $container->setDefinition(TesterExtension::EXAMPLE_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);
    }

    /**
     * Loads event-dispatching step tester.
     *
     * @param ContainerBuilder $container
     */
    protected function loadEventDispatchingStepTester(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\EventDispatcher\Tester\EventDispatchingStepTester', array(
            new Reference(TesterExtension::STEP_TESTER_ID),
            new Reference(self::DISPATCHER_ID)
        ));
        $definition->addTag(TesterExtension::STEP_TESTER_WRAPPER_TAG, array('priority' => -9999));
        $container->setDefinition(TesterExtension::STEP_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);
    }
}

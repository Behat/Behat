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
use Behat\Behat\EventDispatcher\Tester\EventDispatchingBackgroundTester;
use Behat\Behat\EventDispatcher\Tester\EventDispatchingFeatureTester;
use Behat\Behat\EventDispatcher\Tester\EventDispatchingOutlineTester;
use Behat\Behat\EventDispatcher\Tester\EventDispatchingScenarioTester;
use Behat\Behat\EventDispatcher\Tester\EventDispatchingStepTester;
use Behat\Behat\Tester\ServiceContainer\TesterExtension;
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
    public function load(ContainerBuilder $container, array $config)
    {
        parent::load($container, $config);

        $this->loadEventDispatchingBackgroundTester($container);
        $this->loadEventDispatchingFeatureTester($container);
        $this->loadEventDispatchingOutlineTester($container);
        $this->loadEventDispatchingScenarioTester($container);
        $this->loadEventDispatchingExampleTester($container);
        $this->loadEventDispatchingStepTester($container);
    }

    /**
     * Loads event-dispatching background tester.
     */
    protected function loadEventDispatchingBackgroundTester(ContainerBuilder $container)
    {
        $definition = new Definition(EventDispatchingBackgroundTester::class, [
            new Reference(TesterExtension::BACKGROUND_TESTER_ID),
            new Reference(self::DISPATCHER_ID),
        ]);
        $definition->addTag(TesterExtension::BACKGROUND_TESTER_WRAPPER_TAG, ['priority' => -9999]);
        $container->setDefinition(TesterExtension::BACKGROUND_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);
    }

    /**
     * Loads event-dispatching feature tester.
     */
    protected function loadEventDispatchingFeatureTester(ContainerBuilder $container)
    {
        $definition = new Definition(EventDispatchingFeatureTester::class, [
            new Reference(TesterExtension::SPECIFICATION_TESTER_ID),
            new Reference(self::DISPATCHER_ID),
        ]);
        $definition->addTag(TesterExtension::SPECIFICATION_TESTER_WRAPPER_TAG, ['priority' => -9999]);
        $container->setDefinition(TesterExtension::SPECIFICATION_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);
    }

    /**
     * Loads event-dispatching outline tester.
     */
    protected function loadEventDispatchingOutlineTester(ContainerBuilder $container)
    {
        $definition = new Definition(EventDispatchingOutlineTester::class, [
            new Reference(TesterExtension::OUTLINE_TESTER_ID),
            new Reference(self::DISPATCHER_ID),
        ]);
        $definition->addTag(TesterExtension::OUTLINE_TESTER_WRAPPER_TAG, ['priority' => -9999]);
        $container->setDefinition(TesterExtension::OUTLINE_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);
    }

    /**
     * Loads event-dispatching scenario tester.
     */
    protected function loadEventDispatchingScenarioTester(ContainerBuilder $container)
    {
        $definition = new Definition(EventDispatchingScenarioTester::class, [
            new Reference(TesterExtension::SCENARIO_TESTER_ID),
            new Reference(self::DISPATCHER_ID),
            ScenarioTested::BEFORE,
            ScenarioTested::AFTER_SETUP,
            ScenarioTested::BEFORE_TEARDOWN,
            ScenarioTested::AFTER,
        ]);
        $definition->addTag(TesterExtension::SCENARIO_TESTER_WRAPPER_TAG, ['priority' => -9999]);
        $container->setDefinition(TesterExtension::SCENARIO_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);
    }

    /**
     * Loads event-dispatching example tester.
     */
    protected function loadEventDispatchingExampleTester(ContainerBuilder $container)
    {
        $definition = new Definition(EventDispatchingScenarioTester::class, [
            new Reference(TesterExtension::EXAMPLE_TESTER_ID),
            new Reference(self::DISPATCHER_ID),
            ExampleTested::BEFORE,
            ExampleTested::AFTER_SETUP,
            ExampleTested::BEFORE_TEARDOWN,
            ExampleTested::AFTER,
        ]);
        $definition->addTag(TesterExtension::EXAMPLE_TESTER_WRAPPER_TAG, ['priority' => -9999]);
        $container->setDefinition(TesterExtension::EXAMPLE_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);
    }

    /**
     * Loads event-dispatching step tester.
     */
    protected function loadEventDispatchingStepTester(ContainerBuilder $container)
    {
        $definition = new Definition(EventDispatchingStepTester::class, [
            new Reference(TesterExtension::STEP_TESTER_ID),
            new Reference(self::DISPATCHER_ID),
        ]);
        $definition->addTag(TesterExtension::STEP_TESTER_WRAPPER_TAG, ['priority' => -9999]);
        $container->setDefinition(TesterExtension::STEP_TESTER_WRAPPER_TAG . '.event_dispatching', $definition);
    }
}

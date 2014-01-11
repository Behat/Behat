<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\ServiceContainer;

use Behat\Behat\Definition\ServiceContainer\DefinitionExtension;
use Behat\Behat\Hook\ServiceContainer\HookExtension;
use Behat\Testwork\Call\ServiceContainer\CallExtension;
use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\Environment\ServiceContainer\EnvironmentExtension;
use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Testwork\Subject\ServiceContainer\SubjectExtension;
use Behat\Testwork\Suite\ServiceContainer\SuiteExtension;
use Behat\Testwork\Tester\ServiceContainer\TesterExtension as BaseExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Behat tester extension.
 *
 * Provides gherkin testers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TesterExtension extends BaseExtension
{
    /*
     * Available services
     */
    const SCENARIO_TESTER_ID = 'tester.scenario';
    const OUTLINE_TESTER_ID = 'tester.outline';
    const EXAMPLE_TESTER_ID = 'tester.example';
    const BACKGROUND_TESTER_ID = 'tester.background';
    const STEP_TESTER_ID = 'tester.step';

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        parent::load($container, $config);

        $this->loadStopOnFailureController($container);
        $this->loadRerunController($container);
    }

    /**
     * Loads exercise controller.
     *
     * @param ContainerBuilder $container
     * @param Boolean          $strict
     * @param Boolean          $skip
     */
    protected function loadExerciseController(ContainerBuilder $container, $strict = false, $skip = false)
    {
        $definition = new Definition('Behat\Behat\Tester\Cli\ExerciseController', array(
            new Reference(SuiteExtension::REGISTRY_ID),
            new Reference(SubjectExtension::FINDER_ID),
            new Reference(self::EXERCISE_ID),
            $strict,
            $skip
        ));
        $definition->addTag(CliExtension::CONTROLLER_TAG, array('priority' => 0));
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.exercise', $definition);
    }

    /**
     * Loads subject tester.
     *
     * @param ContainerBuilder $container
     */
    protected function loadSubjectTester(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Tester\HookableFeatureTester', array(
            new Reference(self::SCENARIO_TESTER_ID),
            new Reference(self::OUTLINE_TESTER_ID)
        ));
        $definition->addMethodCall(
            'setHookDispatcher',
            array(new Reference(HookExtension::DISPATCHER_ID))
        );
        $definition->addMethodCall(
            'setEventDispatcher',
            array(new Reference(EventDispatcherExtension::DISPATCHER_ID))
        );
        $container->setDefinition(self::SUBJECT_TESTER_ID, $definition);

        $this->loadScenarioTester($container);
        $this->loadOutlineTester($container);
        $this->loadBackgroundTester($container);
        $this->loadStepTester($container);
    }

    /**
     * Loads scenario tester.
     *
     * @param ContainerBuilder $container
     */
    protected function loadScenarioTester(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Tester\HookableStepContainerTester', array(
            new Reference(self::STEP_TESTER_ID),
            new Reference(self::BACKGROUND_TESTER_ID),
            new Reference(EnvironmentExtension::MANAGER_ID)

        ));
        $definition->addMethodCall(
            'setHookDispatcherAndEventClass',
            array(new Reference(HookExtension::DISPATCHER_ID), 'Behat\Behat\Tester\Event\ScenarioTested')
        );
        $definition->addMethodCall(
            'setEventDispatcherAndEventClass',
            array(new Reference(EventDispatcherExtension::DISPATCHER_ID), 'Behat\Behat\Tester\Event\ScenarioTested')
        );
        $container->setDefinition(self::SCENARIO_TESTER_ID, $definition);
    }

    /**
     * Loads outline tester.
     *
     * @param ContainerBuilder $container
     */
    protected function loadOutlineTester(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Tester\DispatchingOutlineTester', array(
            new Reference(self::EXAMPLE_TESTER_ID)
        ));
        $definition->addMethodCall(
            'setEventDispatcher',
            array(new Reference(EventDispatcherExtension::DISPATCHER_ID))
        );
        $container->setDefinition(self::OUTLINE_TESTER_ID, $definition);

        $this->loadExampleTester($container);
    }

    /**
     * Loads example tester.
     *
     * @param ContainerBuilder $container
     */
    protected function loadExampleTester(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Tester\HookableStepContainerTester', array(
            new Reference(self::STEP_TESTER_ID),
            new Reference(self::BACKGROUND_TESTER_ID),
            new Reference(EnvironmentExtension::MANAGER_ID)
        ));
        $definition->addMethodCall(
            'setHookDispatcherAndEventClass',
            array(new Reference(HookExtension::DISPATCHER_ID), 'Behat\Behat\Tester\Event\ExampleTested')
        );
        $definition->addMethodCall(
            'setEventDispatcherAndEventClass',
            array(new Reference(EventDispatcherExtension::DISPATCHER_ID), 'Behat\Behat\Tester\Event\ExampleTested')
        );
        $container->setDefinition(self::EXAMPLE_TESTER_ID, $definition);
    }

    /**
     * Loads background tester.
     *
     * @param ContainerBuilder $container
     */
    protected function loadBackgroundTester(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Tester\DispatchingBackgroundTester', array(
            new Reference(self::STEP_TESTER_ID)
        ));
        $definition->addMethodCall(
            'setEventDispatcher',
            array(new Reference(EventDispatcherExtension::DISPATCHER_ID))
        );
        $container->setDefinition(self::BACKGROUND_TESTER_ID, $definition);
    }

    /**
     * Loads step tester.
     *
     * @param ContainerBuilder $container
     */
    protected function loadStepTester(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Tester\HookableStepTester', array(
            new Reference(DefinitionExtension::FINDER_ID),
            new Reference(CallExtension::CALL_CENTER_ID)
        ));
        $definition->addMethodCall(
            'setHookDispatcher',
            array(new Reference(HookExtension::DISPATCHER_ID))
        );
        $definition->addMethodCall(
            'setEventDispatcher',
            array(new Reference(EventDispatcherExtension::DISPATCHER_ID))
        );
        $container->setDefinition(self::STEP_TESTER_ID, $definition);
    }

    /**
     * Loads stop on failure controller.
     *
     * @param ContainerBuilder $container
     */
    protected function loadStopOnFailureController(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Tester\Cli\StopOnFailureController', array(
            new Reference(EventDispatcherExtension::DISPATCHER_ID)
        ));
        $definition->addTag(CliExtension::CONTROLLER_TAG, array('priority' => 20));
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.stop_on_failure', $definition);
    }

    /**
     * Loads rerun controller.
     *
     * @param ContainerBuilder $container
     */
    protected function loadRerunController(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Tester\Cli\RerunController', array(
            new Reference(EventDispatcherExtension::DISPATCHER_ID)
        ));
        $definition->addTag(CliExtension::CONTROLLER_TAG, array('priority' => 40));
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.rerun', $definition);
    }
}

<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\ServiceContainer;

use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\Environment\ServiceContainer\EnvironmentExtension;
use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Testwork\Hook\ServiceContainer\HookExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\Subject\ServiceContainer\SubjectExtension;
use Behat\Testwork\Suite\ServiceContainer\SuiteExtension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Testwork tester extension.
 *
 * Provides tester services.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class TesterExtension implements Extension
{
    /*
     * Available services
     */
    const EXERCISE_ID = 'tester.exercise';
    const SUITE_TESTER_ID = 'tester.suite';
    const SUBJECT_TESTER_ID = 'tester.subject';

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'tester';
    }

    /**
     * Setups configuration for the extension.
     *
     * @param ArrayNodeDefinition $builder
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('strict')
                    ->defaultFalse()
                ->end()
                ->booleanNode('skip')
                    ->defaultFalse()
                ->end()
            ->end()
        ;
    }

    /**
     * Loads extension services into temporary container.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadExerciseController($container, $config['strict'], $config['skip']);
        $this->loadSigintController($container);
        $this->loadExercise($container);
        $this->loadSuiteTester($container);
        $this->loadSubjectTester($container);
    }

    /**
     * Processes shared container after all extensions loaded.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
    }

    /**
     * Loads exercise cli controllers.
     *
     * @param ContainerBuilder $container
     * @param Boolean          $strict
     * @param Boolean          $skip
     */
    protected function loadExerciseController(ContainerBuilder $container, $strict = false, $skip = false)
    {
        $definition = new Definition('Behat\Testwork\Tester\Cli\ExerciseController', array(
            new Reference(SuiteExtension::REGISTRY_ID),
            new Reference(SubjectExtension::LOCATOR_ID),
            new Reference(self::EXERCISE_ID),
            $strict,
            $skip
        ));
        $definition->addTag(CliExtension::CONTROLLER_TAG, array('priority' => 0));
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.exercise', $definition);
    }

    /**
     * Loads sigint controller
     *
     * @param ContainerBuilder $container
     */
    protected function loadSigintController(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Tester\Cli\SigintController', array(
            new Reference(EventDispatcherExtension::DISPATCHER_ID)
        ));
        $definition->addTag(CliExtension::CONTROLLER_TAG, array('priority' => 50));
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.sigint', $definition);
    }

    /**
     * Loads exercise tester.
     *
     * @param ContainerBuilder $container
     */
    protected function loadExercise(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Tester\Exercise', array(
            new Reference(self::SUITE_TESTER_ID),
            new Reference(EventDispatcherExtension::DISPATCHER_ID)
        ));
        $container->setDefinition(self::EXERCISE_ID, $definition);
    }

    /**
     * Loads suite tester.
     *
     * @param ContainerBuilder $container
     */
    protected function loadSuiteTester(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Tester\SuiteTester', array(
            new Reference(self::SUBJECT_TESTER_ID),
            new Reference(EnvironmentExtension::MANAGER_ID),
            new Reference(HookExtension::DISPATCHER_ID),
            new Reference(EventDispatcherExtension::DISPATCHER_ID)
        ));
        $container->setDefinition(self::SUITE_TESTER_ID, $definition);
    }

    /**
     * Loads subject tester.
     *
     * @param ContainerBuilder $container
     */
    abstract protected function loadSubjectTester(ContainerBuilder $container);
}

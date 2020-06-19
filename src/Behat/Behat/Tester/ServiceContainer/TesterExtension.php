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
use Behat\Testwork\Call\ServiceContainer\CallExtension;
use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\Environment\ServiceContainer\EnvironmentExtension;
use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Testwork\Exception\ServiceContainer\ExceptionExtension;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Behat\Testwork\Tester\ServiceContainer\TesterExtension as BaseExtension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Provides gherkin testers.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TesterExtension extends BaseExtension
{
    /*
     * Available services
     */
    public const SCENARIO_TESTER_ID = 'tester.scenario';
    public const OUTLINE_TESTER_ID = 'tester.outline';
    public const EXAMPLE_TESTER_ID = 'tester.example';
    public const BACKGROUND_TESTER_ID = 'tester.background';
    public const STEP_TESTER_ID = 'tester.step';

    /**
     * Available extension points
     */
    public const SCENARIO_TESTER_WRAPPER_TAG = 'tester.scenario.wrapper';
    public const OUTLINE_TESTER_WRAPPER_TAG = 'tester.outline.wrapper';
    public const EXAMPLE_TESTER_WRAPPER_TAG = 'tester.example.wrapper';
    public const BACKGROUND_TESTER_WRAPPER_TAG = 'tester.background.wrapper';
    public const STEP_TESTER_WRAPPER_TAG = 'tester.step.wrapper';

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

        parent::__construct($this->processor);
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        parent::configure($builder);

        $builder
            ->children()
                ->scalarNode('rerun_cache')
                    ->info('Sets the rerun cache path')
                    ->defaultValue(
                        is_writable(sys_get_temp_dir())
                            ? sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat_rerun_cache'
                            : null
                    )
                ->end()
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        parent::load($container, $config);

        $this->loadRerunController($container, $config['rerun_cache']);
        $this->loadPendingExceptionStringer($container);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        parent::process($container);

        $this->processScenarioTesterWrappers($container);
        $this->processOutlineTesterWrappers($container);
        $this->processExampleTesterWrappers($container);
        $this->processBackgroundTesterWrappers($container);
        $this->processStepTesterWrappers($container);
    }

    /**
     * Loads specification tester.
     *
     * @param ContainerBuilder $container
     */
    protected function loadSpecificationTester(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Tester\Runtime\RuntimeFeatureTester', array(
            new Reference(self::SCENARIO_TESTER_ID),
            new Reference(self::OUTLINE_TESTER_ID),
            new Reference(EnvironmentExtension::MANAGER_ID)
        ));
        $container->setDefinition(self::SPECIFICATION_TESTER_ID, $definition);

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
        $definition = new Definition('Behat\Behat\Tester\StepContainerTester', array(
            new Reference(self::STEP_TESTER_ID)
        ));
        $container->setDefinition('tester.step_container', $definition);

        $definition = new Definition('Behat\Behat\Tester\Runtime\RuntimeScenarioTester', array(
            new Reference('tester.step_container'),
            new Reference(self::BACKGROUND_TESTER_ID)
        ));
        $container->setDefinition(self::SCENARIO_TESTER_ID, $definition);

        // Proper isolation for scenarios
        $definition = new Definition('Behat\Behat\Tester\Runtime\IsolatingScenarioTester', array(
                new Reference(self::SCENARIO_TESTER_ID),
                new Reference(EnvironmentExtension::MANAGER_ID)
            )
        );
        $definition->addTag(self::SCENARIO_TESTER_WRAPPER_TAG, array('priority' => -999999));
        $container->setDefinition(self::SCENARIO_TESTER_WRAPPER_TAG . '.isolating', $definition);
    }

    /**
     * Loads outline tester.
     *
     * @param ContainerBuilder $container
     */
    protected function loadOutlineTester(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Tester\Runtime\RuntimeOutlineTester', array(
            new Reference(self::EXAMPLE_TESTER_ID)
        ));
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
        $definition = new Definition('Behat\Behat\Tester\StepContainerTester', array(
            new Reference(self::STEP_TESTER_ID)
        ));
        $container->setDefinition('tester.step_container', $definition);

        $definition = new Definition('Behat\Behat\Tester\Runtime\RuntimeScenarioTester', array(
            new Reference('tester.step_container'),
            new Reference(self::BACKGROUND_TESTER_ID)
        ));
        $container->setDefinition(self::EXAMPLE_TESTER_ID, $definition);

        // Proper isolation for examples
        $definition = new Definition('Behat\Behat\Tester\Runtime\IsolatingScenarioTester', array(
                new Reference(self::EXAMPLE_TESTER_ID),
                new Reference(EnvironmentExtension::MANAGER_ID)
            )
        );
        $definition->addTag(self::EXAMPLE_TESTER_WRAPPER_TAG, array('priority' => -999999));
        $container->setDefinition(self::EXAMPLE_TESTER_WRAPPER_TAG . '.isolating', $definition);
    }

    /**
     * Loads background tester.
     *
     * @param ContainerBuilder $container
     */
    protected function loadBackgroundTester(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Tester\StepContainerTester', array(
            new Reference(self::STEP_TESTER_ID)
        ));
        $container->setDefinition('tester.step_container', $definition);

        $definition = new Definition('Behat\Behat\Tester\Runtime\RuntimeBackgroundTester', array(
            new Reference('tester.step_container')
        ));
        $container->setDefinition(self::BACKGROUND_TESTER_ID, $definition);
    }

    /**
     * Loads step tester.
     *
     * @param ContainerBuilder $container
     */
    protected function loadStepTester(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Tester\Runtime\RuntimeStepTester', array(
            new Reference(DefinitionExtension::FINDER_ID),
            new Reference(CallExtension::CALL_CENTER_ID)
        ));
        $container->setDefinition(self::STEP_TESTER_ID, $definition);
    }

    /**
     * Loads rerun controller.
     *
     * @param ContainerBuilder $container
     * @param null|string      $cachePath
     */
    protected function loadRerunController(ContainerBuilder $container, $cachePath)
    {
        $definition = new Definition('Behat\Behat\Tester\Cli\RerunController', array(
            new Reference(EventDispatcherExtension::DISPATCHER_ID),
            $cachePath,
            $container->getParameter('paths.base')
        ));
        $definition->addTag(CliExtension::CONTROLLER_TAG, array('priority' => 200));
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.rerun', $definition);
    }

    /**
     * Loads pending exception stringer.
     *
     * @param ContainerBuilder $container
     */
    protected function loadPendingExceptionStringer(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Tester\Exception\Stringer\PendingExceptionStringer');
        $definition->addTag(ExceptionExtension::STRINGER_TAG);
        $container->setDefinition(ExceptionExtension::STRINGER_TAG . '.pending', $definition);
    }

    /**
     * Processes all registered scenario tester wrappers.
     *
     * @param ContainerBuilder $container
     */
    protected function processScenarioTesterWrappers(ContainerBuilder $container)
    {
        $this->processor->processWrapperServices($container, self::SCENARIO_TESTER_ID, self::SCENARIO_TESTER_WRAPPER_TAG);
    }

    /**
     * Processes all registered outline tester wrappers.
     *
     * @param ContainerBuilder $container
     */
    protected function processOutlineTesterWrappers(ContainerBuilder $container)
    {
        $this->processor->processWrapperServices($container, self::OUTLINE_TESTER_ID, self::OUTLINE_TESTER_WRAPPER_TAG);
    }

    /**
     * Processes all registered example tester wrappers.
     *
     * @param ContainerBuilder $container
     */
    protected function processExampleTesterWrappers(ContainerBuilder $container)
    {
        $this->processor->processWrapperServices($container, self::EXAMPLE_TESTER_ID, self::EXAMPLE_TESTER_WRAPPER_TAG);
    }

    /**
     * Processes all registered background tester wrappers.
     *
     * @param ContainerBuilder $container
     */
    protected function processBackgroundTesterWrappers(ContainerBuilder $container)
    {
        $this->processor->processWrapperServices($container, self::BACKGROUND_TESTER_ID, self::BACKGROUND_TESTER_WRAPPER_TAG);
    }

    /**
     * Processes all registered step tester wrappers.
     *
     * @param ContainerBuilder $container
     */
    protected function processStepTesterWrappers(ContainerBuilder $container)
    {
        $this->processor->processWrapperServices($container, self::STEP_TESTER_ID, self::STEP_TESTER_WRAPPER_TAG);
    }
}

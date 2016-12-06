<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Suite\ServiceContainer;

use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Extends testwork with suite-related services.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SuiteExtension implements Extension
{
    /*
     * Available services
     */
    const REGISTRY_ID = 'suite.registry';
    const BOOTSTRAPPER_ID = 'suite.bootstrapper';

    /*
     * Available extension points
     */
    const GENERATOR_TAG = 'suite.generator';
    const SETUP_TAG = 'suite.setup';

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
        return 'suites';
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
        $builder
            ->defaultValue(array('default' => array(
                'enabled'    => true,
                'type'       => null,
                'settings'   => array()
            )))
            ->treatNullLike(array())
            ->treatFalseLike(array())
            ->useAttributeAsKey('name')
            ->normalizeKeys(false)
            ->prototype('array')
                ->beforeNormalization()
                    ->ifTrue(function ($suite) {
                        return is_array($suite) && count($suite);
                    })
                    ->then(function ($suite) {
                        $suite['settings'] = isset($suite['settings'])
                            ? $suite['settings']
                            : array();

                        foreach ($suite as $key => $val) {
                            $suiteKeys = array('enabled', 'type', 'settings');
                            if (!in_array($key, $suiteKeys)) {
                                $suite['settings'][$key] = $val;
                                unset($suite[$key]);
                            }
                        }

                        return $suite;
                    })
                ->end()
                ->normalizeKeys(false)
                ->addDefaultsIfNotSet()
                ->treatTrueLike(array('enabled' => true))
                ->treatNullLike(array('enabled' => true))
                ->treatFalseLike(array('enabled' => false))
                ->children()
                    ->booleanNode('enabled')
                        ->info('Enables/disables suite')
                        ->defaultTrue()
                    ->end()
                    ->scalarNode('type')
                        ->info('Specifies suite type')
                        ->defaultValue(null)
                    ->end()
                    ->arrayNode('settings')
                        ->info('Specifies suite extra settings')
                        ->defaultValue(array())
                        ->useAttributeAsKey('name')
                        ->prototype('variable')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->setSuiteConfigurations($container, $config);
        $this->loadRegistryController($container);
        $this->loadBootstrapController($container);
        $this->loadRegistry($container);
        $this->loadBootstrapper($container);
        $this->loadGenericSuiteGenerator($container);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->processGenerators($container);
        $this->processSetups($container);
    }

    /**
     * Generates and sets suites parameter to container.
     *
     * @param ContainerBuilder $container
     * @param array            $suites
     */
    private function setSuiteConfigurations(ContainerBuilder $container, array $suites)
    {
        $configuredSuites = array();
        foreach ($suites as $name => $config) {
            if (!$config['enabled']) {
                continue;
            }

            $configuredSuites[$name] = array(
                'type'     => $config['type'],
                'settings' => $config['settings'],
            );
        }

        $container->setParameter('suite.configurations', $configuredSuites);
    }

    /**
     * Loads suite registry controller.
     *
     * @param ContainerBuilder $container
     */
    private function loadRegistryController(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Suite\Cli\SuiteController', array(
            new Reference(self::REGISTRY_ID),
            '%suite.configurations%'
        ));
        $definition->addTag(CliExtension::CONTROLLER_TAG, array('priority' => 1100));
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.suite', $definition);
    }

    /**
     * Loads suite bootstrap controller.
     *
     * @param ContainerBuilder $container
     */
    private function loadBootstrapController(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Suite\Cli\InitializationController', array(
            new Reference(self::REGISTRY_ID),
            new Reference(self::BOOTSTRAPPER_ID)
        ));
        $definition->addTag(CliExtension::CONTROLLER_TAG, array('priority' => 900));
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.initialization', $definition);
    }

    /**
     * Loads suite registry.
     *
     * @param ContainerBuilder $container
     */
    private function loadRegistry(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Suite\SuiteRegistry');
        $container->setDefinition(self::REGISTRY_ID, $definition);
    }

    /**
     * Loads suite bootstrapper.
     *
     * @param ContainerBuilder $container
     */
    private function loadBootstrapper(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Suite\SuiteBootstrapper');
        $container->setDefinition(self::BOOTSTRAPPER_ID, $definition);
    }

    /**
     * Loads generic suite generator.
     *
     * @param ContainerBuilder $container
     */
    private function loadGenericSuiteGenerator(ContainerBuilder $container)
    {
        $container->setParameter('suite.generic.default_settings', array());

        $definition = new Definition('Behat\Testwork\Suite\Generator\GenericSuiteGenerator', array(
            '%suite.generic.default_settings%'
        ));
        $definition->addTag(SuiteExtension::GENERATOR_TAG, array('priority' => 50));
        $container->setDefinition(SuiteExtension::GENERATOR_TAG . '.generic', $definition);
    }

    /**
     * Processes suite generators.
     *
     * @param ContainerBuilder $container
     */
    private function processGenerators(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::GENERATOR_TAG);
        $definition = $container->getDefinition(self::REGISTRY_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerSuiteGenerator', array($reference));
        }
    }

    /**
     * Processes suite setups.
     *
     * @param ContainerBuilder $container
     */
    private function processSetups(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::SETUP_TAG);
        $definition = $container->getDefinition(self::BOOTSTRAPPER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerSuiteSetup', array($reference));
        }
    }
}

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
    public const REGISTRY_ID = 'suite.registry';
    public const BOOTSTRAPPER_ID = 'suite.bootstrapper';

    /*
     * Available extension points
     */
    public const GENERATOR_TAG = 'suite.generator';
    public const SETUP_TAG = 'suite.setup';

    /**
     * @var ServiceProcessor
     */
    private $processor;

    /**
     * Initializes extension.
     */
    public function __construct(?ServiceProcessor $processor = null)
    {
        $this->processor = $processor ?: new ServiceProcessor();
    }

    public function getConfigKey()
    {
        return 'suites';
    }

    public function initialize(ExtensionManager $extensionManager)
    {
    }

    public function configure(ArrayNodeDefinition $builder)
    {
        $builder = $builder
            ->defaultValue(['default' => [
                'enabled' => true,
                'type' => null,
                'settings' => [],
            ]])
            ->treatNullLike([])
            ->treatFalseLike([])
            ->useAttributeAsKey('name')
            ->normalizeKeys(false)
            ->prototype('array')
                ->beforeNormalization()
                    ->ifTrue(function ($suite) {
                        return is_array($suite) && count($suite);
                    })
                    ->then(function ($suite) {
                        $suite['settings'] = $suite['settings'] ?? [];

                        foreach ($suite as $key => $val) {
                            $suiteKeys = ['enabled', 'type', 'settings'];
                            if (!in_array($key, $suiteKeys)) {
                                $suite['settings'][$key] = $val;
                                unset($suite[$key]);
                            }
                        }

                        return $suite;
                    })
                ->end()
        ;
        assert($builder instanceof ArrayNodeDefinition);
        $childrenBuilder = $builder
                ->normalizeKeys(false)
                ->addDefaultsIfNotSet()
                ->treatTrueLike(['enabled' => true])
                ->treatNullLike(['enabled' => true])
                ->treatFalseLike(['enabled' => false])
                ->children()
        ;
        $childrenBuilder
                    ->booleanNode('enabled')
                        ->info('Enables/disables suite')
                        ->defaultTrue()
        ;
        $childrenBuilder
                    ->scalarNode('type')
                        ->info('Specifies suite type')
                        ->defaultValue(null)
        ;
        $childrenBuilder
                    ->arrayNode('settings')
                        ->info('Specifies suite extra settings')
                        ->defaultValue([])
                        ->useAttributeAsKey('name')
                        ->prototype('variable')->end()
        ;
    }

    public function load(ContainerBuilder $container, array $config)
    {
        $this->setSuiteConfigurations($container, $config);
        $this->loadRegistryController($container);
        $this->loadBootstrapController($container);
        $this->loadRegistry($container);
        $this->loadBootstrapper($container);
        $this->loadGenericSuiteGenerator($container);
    }

    public function process(ContainerBuilder $container)
    {
        $this->processGenerators($container);
        $this->processSetups($container);
    }

    /**
     * Generates and sets suites parameter to container.
     */
    private function setSuiteConfigurations(ContainerBuilder $container, array $suites)
    {
        $configuredSuites = [];
        foreach ($suites as $name => $config) {
            if (!$config['enabled']) {
                continue;
            }

            $configuredSuites[$name] = [
                'type' => $config['type'],
                'settings' => $config['settings'],
            ];
        }

        $container->setParameter('suite.configurations', $configuredSuites);
    }

    /**
     * Loads suite registry controller.
     */
    private function loadRegistryController(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Suite\Cli\SuiteController', [
            new Reference(self::REGISTRY_ID),
            '%suite.configurations%',
        ]);
        $definition->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 1100]);
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.suite', $definition);
    }

    /**
     * Loads suite bootstrap controller.
     */
    private function loadBootstrapController(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Suite\Cli\InitializationController', [
            new Reference(self::REGISTRY_ID),
            new Reference(self::BOOTSTRAPPER_ID),
        ]);
        $definition->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 900]);
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.initialization', $definition);
    }

    /**
     * Loads suite registry.
     */
    private function loadRegistry(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Suite\SuiteRegistry');
        $container->setDefinition(self::REGISTRY_ID, $definition);
    }

    /**
     * Loads suite bootstrapper.
     */
    private function loadBootstrapper(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Suite\SuiteBootstrapper');
        $container->setDefinition(self::BOOTSTRAPPER_ID, $definition);
    }

    /**
     * Loads generic suite generator.
     */
    private function loadGenericSuiteGenerator(ContainerBuilder $container)
    {
        $container->setParameter('suite.generic.default_settings', []);

        $definition = new Definition('Behat\Testwork\Suite\Generator\GenericSuiteGenerator', [
            '%suite.generic.default_settings%',
        ]);
        $definition->addTag(SuiteExtension::GENERATOR_TAG, ['priority' => 50]);
        $container->setDefinition(SuiteExtension::GENERATOR_TAG . '.generic', $definition);
    }

    /**
     * Processes suite generators.
     */
    private function processGenerators(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::GENERATOR_TAG);
        $definition = $container->getDefinition(self::REGISTRY_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerSuiteGenerator', [$reference]);
        }
    }

    /**
     * Processes suite setups.
     */
    private function processSetups(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::SETUP_TAG);
        $definition = $container->getDefinition(self::BOOTSTRAPPER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerSuiteSetup', [$reference]);
        }
    }
}

<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\ServiceContainer;

use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Testwork\Output\Cli\OutputController;
use Behat\Testwork\Output\OutputManager;
use Behat\Testwork\Output\ServiceContainer\Formatter\FormatterFactory;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Provides output management services for testwork.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class OutputExtension implements Extension
{
    /*
     * Available services
     */
    public const MANAGER_ID = 'output.manager';

    /*
     * Available extension points
     */
    public const FORMATTER_TAG = 'output.formatter';
    /**
     * @var ServiceProcessor
     */
    private $processor;

    /**
     * Initializes extension.
     *
     * @param string                $defaultFormatter
     * @param FormatterFactory[] $factories
     */
    public function __construct(
        private $defaultFormatter,
        private array $factories,
        ?ServiceProcessor $processor = null,
    ) {
        $this->processor = $processor ?: new ServiceProcessor();
    }

    /**
     * Registers formatter factory.
     */
    public function registerFormatterFactory(FormatterFactory $factory)
    {
        $this->factories[] = $factory;
    }

    public function getConfigKey()
    {
        return 'formatters';
    }

    public function initialize(ExtensionManager $extensionManager)
    {
    }

    public function configure(ArrayNodeDefinition $builder)
    {
        $builder = $builder
            ->defaultValue([$this->defaultFormatter => ['enabled' => true]])
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->beforeNormalization()
                    ->ifTrue(fn ($a) => is_array($a) && !isset($a['enabled']))
                    ->then(fn ($a) => array_merge($a, ['enabled' => true]))
                ->end()
        ;
        /** @var ArrayNodeDefinition $builder */
        $builder
                ->useAttributeAsKey('name')
                ->treatTrueLike(['enabled' => true])
                ->treatNullLike(['enabled' => true])
                ->treatFalseLike(['enabled' => false])
                ->prototype('variable')->end()
        ;
    }

    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadOutputController($container);
        $this->loadFormatters($container);
        $this->loadManager($container, $config);
    }

    public function process(ContainerBuilder $container): void
    {
        $this->processFormatters($container);
        $this->processDynamicallyRegisteredFormatters($container);
    }

    /**
     * Loads output controller.
     */
    private function loadOutputController(ContainerBuilder $container)
    {
        $definition = new Definition(OutputController::class, [
            new Reference(self::MANAGER_ID),
        ]);
        $definition->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 1000]);
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.output', $definition);
    }

    /**
     * Loads output manager.
     */
    private function loadManager(ContainerBuilder $container, array $formatters)
    {
        $definition = new Definition(OutputManager::class, [
            new Reference(EventDispatcherExtension::DISPATCHER_ID),
        ]);

        foreach ($formatters as $name => $parameters) {
            if ($parameters['enabled']) {
                $definition->addMethodCall('enableFormatter', [$name]);
            } else {
                $definition->addMethodCall('disableFormatter', [$name]);
            }

            unset($parameters['enabled']);
            $definition->addMethodCall('setFormatterParameters', [$name, $parameters]);
        }

        $container->setDefinition(self::MANAGER_ID, $definition);
    }

    /**
     * Loads default formatters using registered factories.
     */
    private function loadFormatters(ContainerBuilder $container)
    {
        foreach ($this->factories as $factory) {
            $factory->buildFormatter($container);
        }
    }

    /**
     * Processes formatters using registered factories.
     */
    private function processFormatters(ContainerBuilder $container)
    {
        foreach ($this->factories as $factory) {
            $factory->processFormatter($container);
        }
    }

    /**
     * Processes all available output formatters.
     */
    private function processDynamicallyRegisteredFormatters(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::FORMATTER_TAG);
        $definition = $container->getDefinition(self::MANAGER_ID);

        $previousCalls = $definition->getMethodCalls();
        $definition->setMethodCalls();

        foreach ($references as $reference) {
            $definition->addMethodCall('registerFormatter', [$reference]);
        }

        foreach ($previousCalls as $call) {
            $definition->addMethodCall($call[0], $call[1]);
        }
    }
}

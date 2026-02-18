<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Deprecation\ServiceContainer;

use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\Deprecation\Cli\DeprecationController;
use Behat\Testwork\Deprecation\DeprecationCollector;
use Behat\Testwork\Deprecation\DeprecationPrinter;
use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Provides deprecation collection and reporting services.
 */
final class DeprecationExtension implements Extension
{
    public const COLLECTOR_ID = 'deprecation.collector';
    public const PRINTER_ID = 'deprecation.printer';
    public const CONTROLLER_ID = 'deprecation.controller';

    public function getConfigKey(): string
    {
        return 'deprecations';
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('print_deprecations')
                    ->defaultFalse()
                ->end()
            ->end()
        ;
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->loadCollector($container);
        $this->loadPrinter($container);
        $this->loadController($container, $config['print_deprecations']);
    }

    public function process(ContainerBuilder $container): void
    {
    }

    private function loadCollector(ContainerBuilder $container): void
    {
        $definition = new Definition(DeprecationCollector::class);
        $definition->setFactory([DeprecationCollector::class, 'getInstance']);
        $container->setDefinition(self::COLLECTOR_ID, $definition);
    }

    private function loadPrinter(ContainerBuilder $container): void
    {
        $definition = new Definition(DeprecationPrinter::class, [
            new Reference(self::COLLECTOR_ID),
        ]);
        $container->setDefinition(self::PRINTER_ID, $definition);
    }

    private function loadController(ContainerBuilder $container, bool $printDeprecations): void
    {
        $definition = new Definition(DeprecationController::class, [
            new Reference(self::COLLECTOR_ID),
            new Reference(self::PRINTER_ID),
            $printDeprecations,
        ]);
        $definition->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 9999]);
        $definition->addTag(EventDispatcherExtension::SUBSCRIBER_TAG);
        $container->setDefinition(self::CONTROLLER_ID, $definition);
    }
}

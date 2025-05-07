<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\PathOptions\ServiceContainer;

use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Provides management of paths in the output.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class PathOptionsExtension implements Extension
{
    public const CONFIGURABLE_PATH_PRINTER_ID = 'configurable.path.printer';

    public function getConfigKey()
    {
        return 'path_options';
    }

    public function initialize(ExtensionManager $extensionManager)
    {
    }

    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('print_absolute_paths')
            ->defaultFalse()
        ;
    }

    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadConfigurablePathPrinter($container, $config['print_absolute_paths']);
        $this->loadPathOptionsController($container);
    }

    public function process(ContainerBuilder $container)
    {
    }

    private function loadConfigurablePathPrinter(ContainerBuilder $container, bool $printAbsolutePaths): void
    {
        $definition = new Definition('Behat\Testwork\PathOptions\Printer\ConfigurablePathPrinter', [
            '%paths.base%',
            $printAbsolutePaths,
        ]);
        $container->setDefinition(self::CONFIGURABLE_PATH_PRINTER_ID, $definition);
    }

    private function loadPathOptionsController(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\PathOptions\Cli\PathOptionsController', [
            new Reference(self::CONFIGURABLE_PATH_PRINTER_ID),
        ]);
        $definition->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 1000]);
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.path', $definition);
    }
}

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
use Behat\Testwork\PathOptions\Cli\PathOptionsController;
use Behat\Testwork\PathOptions\Printer\ConfigurablePathPrinter;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
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

    public function getConfigKey(): string
    {
        return 'path_options';
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder = $builder
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('print_absolute_paths')
            ->defaultFalse()
            ->end();
        /** @var NodeBuilder $builder */
        $builder
            ->scalarNode('editor_url')
            ->defaultNull()
            ->end()
        ;
        $builder
            ->arrayNode('remove_prefix')
            ->scalarPrototype()
            ->defaultValue([])
            ->end()
        ;
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->loadConfigurablePathPrinter(
            $container,
            $config['print_absolute_paths'],
            $config['editor_url'] ?? null,
            $config['remove_prefix']
        );
        $this->loadPathOptionsController($container);
    }

    public function process(ContainerBuilder $container): void
    {
    }

    /**
     * @param string[] $removePrefix
     */
    private function loadConfigurablePathPrinter(
        ContainerBuilder $container,
        bool $printAbsolutePaths,
        ?string $editorUrl,
        array $removePrefix = [],
    ): void {
        $definition = new Definition(ConfigurablePathPrinter::class, [
            '%paths.base%',
            $printAbsolutePaths,
            $editorUrl,
            $removePrefix,
        ]);
        $container->setDefinition(self::CONFIGURABLE_PATH_PRINTER_ID, $definition);
    }

    private function loadPathOptionsController(ContainerBuilder $container): void
    {
        $definition = new Definition(PathOptionsController::class, [
            new Reference(self::CONFIGURABLE_PATH_PRINTER_ID),
        ]);
        $definition->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 1000]);
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.path', $definition);
    }
}

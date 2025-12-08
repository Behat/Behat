<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Filesystem\ServiceContainer;

use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\Filesystem\ConsoleFilesystemLogger;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Provides filesystem services for testwork.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class FilesystemExtension implements Extension
{
    /*
     * Available services
     */
    public const LOGGER_ID = 'filesystem.logger';

    public function getConfigKey(): string
    {
        return 'filesystem';
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->loadFilesystemLogger($container);
    }

    public function process(ContainerBuilder $container): void
    {
    }

    /**
     * Loads filesystem logger.
     */
    protected function loadFilesystemLogger(ContainerBuilder $container): void
    {
        $definition = new Definition(ConsoleFilesystemLogger::class, [
            '%paths.base%',
            new Reference(CliExtension::OUTPUT_ID),
        ]);
        $container->setDefinition(self::LOGGER_ID, $definition);
    }
}

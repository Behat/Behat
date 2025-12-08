<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Environment\ServiceContainer;

use Behat\Testwork\Environment\EnvironmentManager;
use Behat\Testwork\Environment\Handler\StaticEnvironmentHandler;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Testwork test environment extension.
 *
 * Extends testwork with environment services.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class EnvironmentExtension implements Extension
{
    /*
     * Available services
     */
    public const MANAGER_ID = 'environment.manager';

    /*
     * Available extension points
     */
    public const HANDLER_TAG = 'environment.handler';
    public const READER_TAG = 'environment.reader';

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

    public function getConfigKey(): string
    {
        return 'environments';
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->loadManager($container);
        $this->loadStaticEnvironmentHandler($container);
    }

    public function process(ContainerBuilder $container): void
    {
        $this->processHandlers($container);
        $this->processReaders($container);
    }

    /**
     * Loads environment manager.
     */
    protected function loadManager(ContainerBuilder $container): void
    {
        $definition = new Definition(EnvironmentManager::class);
        $container->setDefinition(self::MANAGER_ID, $definition);
    }

    /**
     * Loads static environments handler.
     */
    protected function loadStaticEnvironmentHandler(ContainerBuilder $container): void
    {
        $definition = new Definition(StaticEnvironmentHandler::class);
        $definition->addTag(self::HANDLER_TAG, ['priority' => 0]);
        $container->setDefinition(self::HANDLER_TAG . '.static', $definition);
    }

    /**
     * Processes all environment handlers.
     */
    protected function processHandlers(ContainerBuilder $container): void
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::HANDLER_TAG);
        $definition = $container->getDefinition(self::MANAGER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerEnvironmentHandler', [$reference]);
        }
    }

    /**
     * Processes all environment readers.
     */
    protected function processReaders(ContainerBuilder $container): void
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::READER_TAG);
        $definition = $container->getDefinition(self::MANAGER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerEnvironmentReader', [$reference]);
        }
    }
}

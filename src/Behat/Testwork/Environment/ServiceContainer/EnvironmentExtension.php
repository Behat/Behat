<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Environment\ServiceContainer;

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
    const MANAGER_ID = 'environment.manager';

    /*
     * Available extension points
     */
    const HANDLER_TAG = 'environment.handler';
    const READER_TAG = 'environment.reader';

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
        return 'environments';
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
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadManager($container);
        $this->loadStaticEnvironmentHandler($container);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->processHandlers($container);
        $this->processReaders($container);
    }

    /**
     * Loads environment manager.
     *
     * @param ContainerBuilder $container
     */
    protected function loadManager(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Environment\EnvironmentManager');
        $container->setDefinition(self::MANAGER_ID, $definition);
    }

    /**
     * Loads static environments handler.
     *
     * @param ContainerBuilder $container
     */
    protected function loadStaticEnvironmentHandler(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Environment\Handler\StaticEnvironmentHandler');
        $definition->addTag(self::HANDLER_TAG, array('priority' => 0));
        $container->setDefinition(self::HANDLER_TAG . '.static', $definition);
    }

    /**
     * Processes all environment handlers.
     *
     * @param ContainerBuilder $container
     */
    protected function processHandlers(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::HANDLER_TAG);
        $definition = $container->getDefinition(self::MANAGER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerEnvironmentHandler', array($reference));
        }
    }

    /**
     * Processes all environment readers.
     *
     * @param ContainerBuilder $container
     */
    protected function processReaders(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::READER_TAG);
        $definition = $container->getDefinition(self::MANAGER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerEnvironmentReader', array($reference));
        }
    }
}

<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Cli\ServiceContainer;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Provides console services for testwork.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class CliExtension implements Extension
{
    /*
     * Available services
     */
    public const COMMAND_ID = 'cli.command';
    public const INPUT_ID = 'cli.input';
    public const OUTPUT_ID = 'cli.output';

    /*
     * Available extension points
     */
    public const CONTROLLER_TAG = 'cli.controller';

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
        $this->processor = $processor ?: new ServiceProcessor();
    }

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'cli';
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
        $this->loadCommand($container);
        $this->loadSyntheticServices($container);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->processControllers($container);
    }

    /**
     * Loads application command.
     *
     * @param ContainerBuilder $container
     */
    protected function loadCommand(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Cli\Command', array('%cli.command.name%', array()));
        $definition->setPublic(true);
        $container->setDefinition(self::COMMAND_ID, $definition);
    }

    protected function loadSyntheticServices(ContainerBuilder $container)
    {
        $container->register(self::INPUT_ID)->setSynthetic(true)->setPublic(true);
        $container->register(self::OUTPUT_ID)->setSynthetic(true)->setPublic(true);
    }

    /**
     * Processes all controllers in container.
     *
     * @param ContainerBuilder $container
     */
    protected function processControllers(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::CONTROLLER_TAG);
        $container->getDefinition(self::COMMAND_ID)->replaceArgument(1, $references);
    }
}

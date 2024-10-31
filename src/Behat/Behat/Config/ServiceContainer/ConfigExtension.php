<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Config\ServiceContainer;

use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Enables stop on failure controller for Behat.
 */
final class ConfigExtension implements Extension
{
    public const STOP_ON_FAILURE_ID = 'config.stop_on_failure';

    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return 'config';
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
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('stop_on_failure')
                    ->defaultValue(null)
                ->end()
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadStopOnFailureHandler($container, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }

    
    /**
     * Loads stop on failure controller.
     */
    private function loadStopOnFailureHandler(ContainerBuilder $container, array $config)
    {
        $definition = new Definition('Behat\Behat\Config\Handler\StopOnFailureHandler', array(
            new Reference(EventDispatcherExtension::DISPATCHER_ID)
        ));
        if ($config['stop_on_failure'] === true) {
            $definition->addMethodCall('registerListeners');
        }
        $container->setDefinition(self::STOP_ON_FAILURE_ID, $definition);
    }
}

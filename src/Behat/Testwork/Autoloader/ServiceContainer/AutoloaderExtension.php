<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Autoloader\ServiceContainer;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Extends testwork with class-loading services.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class AutoloaderExtension implements Extension
{
    /*
     * Available services
     */
    const CLASS_LOADER_ID = 'class_loader';

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'autoload';
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
            ->beforeNormalization()
                ->ifString()
                ->then(function($path) {
                    return array('' => $path);
                })
            ->end()
            ->defaultValue(array())
            ->treatTrueLike(array())
            ->treatNullLike(array())
            ->treatFalseLike(array())
            ->prototype('scalar')->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $definition = new Definition('Symfony\Component\ClassLoader\ClassLoader');
        $definition->addMethodCall('register');
        $container->setDefinition(self::CLASS_LOADER_ID, $definition);

        $container->setParameter('class_loader.prefixes', $config);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $loaderDefinition = $container->getDefinition(self::CLASS_LOADER_ID);

        foreach ($container->getParameter('class_loader.prefixes') as $prefix => $path) {
            $loaderDefinition->addMethodCall('addPrefix', array($prefix, $path));
        }
    }
}

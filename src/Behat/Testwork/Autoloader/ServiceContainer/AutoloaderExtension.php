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
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Testwork autoloader extension.
 *
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
     * Setups configuration for the extension.
     *
     * @param ArrayNodeDefinition $builder
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
            ->defaultValue(array('' => '%paths.base%/features/bootstrap'))
            ->treatTrueLike(array('' => '%paths.base%/features/bootstrap'))
            ->treatNullLike(array('' => '%paths.base%/features/bootstrap'))
            ->treatFalseLike(array())
            ->prototype('scalar')->end()
        ;
    }

    /**
     * Loads extension services into temporary container.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $definition = new Definition('Symfony\Component\ClassLoader\ClassLoader');
        $definition->addMethodCall('register');
        $container->setDefinition(self::CLASS_LOADER_ID, $definition);

        $container->setParameter('class_loader.prefixes', $config);
    }

    /**
     * Processes shared container after all extensions loaded.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $loaderDefinition = $container->getDefinition(self::CLASS_LOADER_ID);

        foreach ($container->getParameter('class_loader.prefixes') as $prefix => $path) {
            $loaderDefinition->addMethodCall('addPrefix', array($prefix, $path));
        }
    }
}

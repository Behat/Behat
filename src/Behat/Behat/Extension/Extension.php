<?php

namespace Behat\Behat\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Loader\XmlFileLoader,
    Symfony\Component\DependencyInjection\Loader\YamlFileLoader,
    Symfony\Component\Config\FileLocator,
    Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat base extension class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class Extension implements ExtensionInterface
{
    /**
     * Loads a specific configuration.
     *
     * @param array            $config    Extension configuration hash (from behat.yml)
     * @param ContainerBuilder $container ContainerBuilder instance
     */
    public function load(array $config, ContainerBuilder $container)
    {
        if (file_exists($config = __DIR__.DIRECTORY_SEPARATOR.'services.xml')) {
            $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/config'));
            $loader->load($config);
        }
        if (file_exists($config = __DIR__.DIRECTORY_SEPARATOR.'services.yml')) {
            $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/config'));
            $loader->load($config);
        }
    }

    /**
     * Setups configuration for current extension.
     *
     * @param ArrayNodeDefinition $builder
     */
    public function getConfig(ArrayNodeDefinition $builder)
    {
        $builder
            ->useAttributeAsKey('name')
            ->prototype('variable')
        ;
    }

    /**
     * Returns compiler passes used by this extension.
     *
     * @return array
     */
    public function getCompilerPasses()
    {
        return array();
    }
}

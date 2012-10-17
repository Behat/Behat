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
class Extension implements ExtensionInterface
{
    private $path;

    /**
     * Initializes extension.
     *
     * @param string $path path to extension directory
     */
    public function __construct($path = null)
    {
        $this->path = $path;
    }

    /**
     * Loads a specific configuration.
     *
     * @param array            $config    Extension configuration hash (from behat.yml)
     * @param ContainerBuilder $container ContainerBuilder instance
     */
    public function load(array $config, ContainerBuilder $container)
    {
        if (file_exists($this->getExtensionPath().($config = get_class($this).'Services.xml'))) {
            $loader = new XmlFileLoader($container, new FileLocator($this->getExtensionPath()));
            $loader->load($config);
        }
        if (file_exists($this->getExtensionPath().($config = get_class($this).'Services.yml'))) {
            $loader = new YamlFileLoader($container, new FileLocator($this->getExtensionPath()));
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

    /**
     * Returns path to the extension directory.
     *
     * @return string
     */
    private function getExtensionPath()
    {
        return rtrim($this->path ?: __DIR__, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    }
}

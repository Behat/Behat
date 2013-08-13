<?php

namespace Behat\Behat\Extension;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use ReflectionClass;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

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
        $path = rtrim($this->getServiceDefinitionsPath(), DIRECTORY_SEPARATOR);
        $name = $this->getServiceDefinitionsName();

        if (file_exists($path . DIRECTORY_SEPARATOR . ($file = $name . '.xml'))) {
            $loader = new XmlFileLoader($container, new FileLocator($path));
            $loader->load($file);
        }
        if (file_exists($path . DIRECTORY_SEPARATOR . ($file = $name . '.yml'))) {
            $loader = new YamlFileLoader($container, new FileLocator($path));
            $loader->load($file);
        }

        $container->setParameter($this->getName() . '.parameters', $config);
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
            ->prototype('variable');
    }

    /**
     * Returns compiler passes used by this extension.
     *
     * @return CompilerPassInterface[]
     */
    public function getCompilerPasses()
    {
        return array();
    }

    /**
     * Returns name of the service definition config without extension and path.
     *
     * @return string
     */
    protected function getServiceDefinitionsName()
    {
        return 'services';
    }

    /**
     * Returns service definition configs path.
     *
     * @return string
     */
    protected function getServiceDefinitionsPath()
    {
        $reflection = new ReflectionClass($this);

        return dirname($reflection->getFileName());
    }
}

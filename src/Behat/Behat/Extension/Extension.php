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

        if (file_exists($path.DIRECTORY_SEPARATOR.($file = $name.'.xml'))) {
            $loader = new XmlFileLoader($container, new FileLocator($path));
            $loader->load($file);
        }
        if (file_exists($path.DIRECTORY_SEPARATOR.($file = $name.'.yml'))) {
            $loader = new YamlFileLoader($container, new FileLocator($path));
            $loader->load($file);
        }

        $container->setParameter($this->getExtensionName().'.parameters', $config);
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
     * Returns extension name used to store extension parameters in DIC.
     *
     * @return string
     */
    protected function getExtensionName()
    {
        return strtolower(ltrim(preg_replace('/[A-Z]/', "_$0", get_class($this)), '_'));
    }

    /**
     * Returns name of the service definition config without extension and path.
     *
     * @return string
     */
    protected function getServiceDefinitionsName()
    {
        return get_class($this).'Services';
    }

    /**
     * Returns service definition configs path.
     *
     * @return string
     */
    protected function getServiceDefinitionsPath()
    {
        $refl = new \ReflectionClass($this);

        return dirname($refl->getFileName());
    }
}

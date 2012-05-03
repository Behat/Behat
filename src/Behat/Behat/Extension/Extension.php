<?php

namespace Behat\Behat\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Loader\XmlFileLoader,
    Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

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
     * @param array            $config    Configuration hash
     * @param ContainerBuilder $container ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
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
     * Returns compiler passes used by this extension.
     *
     * @return array
     */
    public function getCompilerPasses()
    {
        return array();
    }
}

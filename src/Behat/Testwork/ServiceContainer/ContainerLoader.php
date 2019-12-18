<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\ServiceContainer;

use Behat\Testwork\ServiceContainer\Configuration\ConfigurationTree;
use Behat\Testwork\ServiceContainer\Exception\ExtensionException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Loads Symfony DI container with testwork extension services.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ContainerLoader
{
    /**
     * @var ExtensionManager
     */
    private $extensionManager;
    /**
     * @var ConfigurationTree
     */
    private $configuration;
    /**
     * @var Processor
     */
    private $processor;

    /**
     * Initialize extension.
     *
     * @param ExtensionManager       $extensionManager
     * @param null|ConfigurationTree $configuration
     * @param null|Processor         $processor
     */
    public function __construct(
        ExtensionManager $extensionManager,
        ConfigurationTree $configuration = null,
        Processor $processor = null
    ) {
        $this->extensionManager = $extensionManager;
        $this->configuration = $configuration ? : new ConfigurationTree();
        $this->processor = $processor ? : new Processor();
    }

    /**
     * Loads container extension.
     *
     * @param ContainerBuilder $container
     * @param array            $configs
     */
    public function load(ContainerBuilder $container, array $configs)
    {
        $configs = $this->initializeExtensions($container, $configs);
        $config = $this->processConfig($configs);

        $this->loadExtensions($container, $config);
    }

    /**
     * Processes config against extensions.
     *
     * @param array $configs
     *
     * @return array
     */
    private function processConfig(array $configs)
    {
        $tree = $this->configuration->getConfigTree($this->extensionManager->getExtensions());

        return $this->processor->process($tree, $configs);
    }

    /**
     * Initializes extensions using provided config.
     *
     * @param ContainerBuilder $container
     * @param array            $configs
     *
     * @return array
     */
    private function initializeExtensions(ContainerBuilder $container, array $configs)
    {
        foreach ($configs as $i => $config) {
            if (isset($config['extensions'])) {
                foreach ($config['extensions'] as $extensionLocator => $extensionConfig) {
                    $extension = $this->extensionManager->activateExtension($extensionLocator);
                    $configs[$i][$extension->getConfigKey()] = $extensionConfig;
                }

                unset($configs[$i]['extensions']);
            }
        }

        $this->extensionManager->initializeExtensions();

        $container->setParameter('extensions', $this->extensionManager->getExtensionClasses());

        return $configs;
    }

    /**
     * Loads all extensions into container using provided config.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     *
     * @throws ExtensionException
     */
    private function loadExtensions(ContainerBuilder $container, array $config)
    {
        // Load default extensions first
        foreach ($this->extensionManager->getExtensions() as $extension) {
            $extensionConfig = array();
            if (isset($config[$extension->getConfigKey()])) {
                $extensionConfig = $config[$extension->getConfigKey()];
                unset($config[$extension->getConfigKey()]);
            }

            $this->loadExtension($container, $extension, $extensionConfig);
        }

        // Load activated extensions
        foreach ($config as $extensionConfigKey => $extensionConfig) {
            if (null === $extension = $this->extensionManager->getExtension($extensionConfigKey)) {
                throw new ExtensionException(
                    sprintf('None of the activated extensions use `%s` config section.', $extensionConfigKey), $extensionConfigKey
                );
            }

            $this->loadExtension($container, $extension, $extensionConfig);
        }
    }

    /**
     * Loads extension configuration.
     *
     * @param ContainerBuilder $container
     * @param Extension        $extension
     * @param array            $config
     */
    private function loadExtension(ContainerBuilder $container, Extension $extension, array $config)
    {
        $tempContainer = new ContainerBuilder(new ParameterBag(array(
            'paths.base' => $container->getParameter('paths.base'),
            'extensions' => $container->getParameter('extensions'),
        )));
        $tempContainer->addObjectResource($extension);
        $extension->load($container, $config);
        $container->merge($tempContainer);
        $container->addCompilerPass($extension);
    }
}

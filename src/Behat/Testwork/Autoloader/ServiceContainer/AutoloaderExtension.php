<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Autoloader\ServiceContainer;

use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Extends Testwork with class-loading services.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AutoloaderExtension implements Extension
{
    /*
     * Available services
     */
    const CLASS_LOADER_ID = 'class_loader';

    /**
     * @var array
     */
    private $defaultPaths = array();

    /**
     * Initializes extension.
     *
     * @param array $defaultPaths
     */
    public function __construct(array $defaultPaths = array())
    {
        $this->defaultPaths = $defaultPaths;
    }

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
                ->ifString()->then(function ($path) {
                    return array('' => $path);
                })
            ->end()

            ->defaultValue($this->defaultPaths)
            ->treatTrueLike($this->defaultPaths)
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
        $this->loadAutoloader($container);
        $this->loadController($container);
        $this->setLoaderPrefixes($container, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->processLoaderPrefixes($container);
    }

    /**
     * Loads Symfony2 autoloader.
     *
     * @param ContainerBuilder $container
     */
    private function loadAutoloader(ContainerBuilder $container)
    {
        $definition = new Definition('Composer\Autoload\ClassLoader');
        $container->setDefinition(self::CLASS_LOADER_ID, $definition);
    }

    /**
     * Loads controller.
     *
     * @param ContainerBuilder $container
     */
    private function loadController(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Autoloader\Cli\AutoloaderController', array(
            new Reference(self::CLASS_LOADER_ID)
        ));
        $definition->addTag(CliExtension::CONTROLLER_TAG, array('priority' => 9999));

        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.autoloader', $definition);
    }

    /**
     * Sets provided prefixes to container.
     *
     * @param ContainerBuilder $container
     * @param array            $prefixes
     */
    private function setLoaderPrefixes(ContainerBuilder $container, array $prefixes)
    {
        $container->setParameter('class_loader.prefixes', $prefixes);
    }

    /**
     * Processes container loader prefixes.
     *
     * @param ContainerBuilder $container
     */
    private function processLoaderPrefixes(ContainerBuilder $container)
    {
        $loaderDefinition = $container->getDefinition(self::CLASS_LOADER_ID);
        $prefixes = $container->getParameter('class_loader.prefixes');

        foreach ($prefixes as $prefix => $path) {
            $loaderDefinition->addMethodCall('add', array($prefix, $path));
        }
    }
}

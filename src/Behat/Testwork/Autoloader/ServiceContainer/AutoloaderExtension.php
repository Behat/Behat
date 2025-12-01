<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Autoloader\ServiceContainer;

use Behat\Testwork\Autoloader\Cli\AutoloaderController;
use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Composer\Autoload\ClassLoader;
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
    public const CLASS_LOADER_ID = 'class_loader';

    /**
     * Initializes extension.
     */
    public function __construct(
        private readonly array $defaultPaths = [],
    ) {
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

    public function initialize(ExtensionManager $extensionManager)
    {
    }

    public function configure(ArrayNodeDefinition $builder)
    {
        $builder = $builder
            ->beforeNormalization()
                ->ifString()->then(fn ($path) => ['' => $path])
            ->end()

            ->defaultValue($this->defaultPaths)
            ->treatTrueLike($this->defaultPaths)
            ->treatNullLike([])
            ->treatFalseLike([])
        ;
        /** @var ArrayNodeDefinition $builder */
        $builder
            ->prototype('scalar')->end()
        ;
    }

    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadAutoloader($container);
        $this->loadController($container);
        $this->setLoaderPrefixes($container, $config);
    }

    public function process(ContainerBuilder $container): void
    {
        $this->processLoaderPrefixes($container);
    }

    /**
     * Loads Symfony2 autoloader.
     */
    private function loadAutoloader(ContainerBuilder $container)
    {
        $definition = new Definition(ClassLoader::class);
        $container->setDefinition(self::CLASS_LOADER_ID, $definition);
    }

    /**
     * Loads controller.
     */
    private function loadController(ContainerBuilder $container)
    {
        $definition = new Definition(AutoloaderController::class, [
            new Reference(self::CLASS_LOADER_ID),
        ]);
        $definition->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 9999]);

        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.autoloader', $definition);
    }

    /**
     * Sets provided prefixes to container.
     */
    private function setLoaderPrefixes(ContainerBuilder $container, array $prefixes)
    {
        $container->setParameter('class_loader.prefixes', $prefixes);
    }

    /**
     * Processes container loader prefixes.
     */
    private function processLoaderPrefixes(ContainerBuilder $container)
    {
        $loaderDefinition = $container->getDefinition(self::CLASS_LOADER_ID);
        $prefixes = $container->getParameter('class_loader.prefixes');

        foreach ($prefixes as $prefix => $path) {
            $loaderDefinition->addMethodCall('add', [$prefix, $path]);
        }
    }
}

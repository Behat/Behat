<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Subject\ServiceContainer;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Testwork subject extension.
 *
 * Extends testwork with test subject services.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SubjectExtension implements Extension
{
    /*
     * Available services
     */
    const LOCATOR_ID = 'subject.locator';

    /*
     * Available extension points
     */
    const ITERATOR_FACTORY_TAG = 'subject.iterator_factory';

    /**
     * @var ServiceProcessor
     */
    private $processor;

    /**
     * Initializes extension.
     *
     * @param null|ServiceProcessor $processor
     */
    public function __construct(ServiceProcessor $processor = null)
    {
        $this->processor = $processor ?: new ServiceProcessor();
    }

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'subjects';
    }

    /**
     * Setups configuration for the extension.
     *
     * @param ArrayNodeDefinition $builder
     */
    public function configure(ArrayNodeDefinition $builder)
    {
    }

    /**
     * Loads extension services into temporary container.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadLocator($container);
    }

    /**
     * Processes shared container after all extensions loaded.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $this->processLoaders($container);
    }

    /**
     * Loads subject repository.
     *
     * @param ContainerBuilder $container
     */
    protected function loadLocator(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Subject\SubjectLocator');
        $container->setDefinition(self::LOCATOR_ID, $definition);
    }

    /**
     * Processes subject loaders.
     *
     * @param ContainerBuilder $container
     */
    protected function processLoaders(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::ITERATOR_FACTORY_TAG);
        $definition = $container->getDefinition(self::LOCATOR_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerIteratorFactory', array($reference));
        }
    }
}

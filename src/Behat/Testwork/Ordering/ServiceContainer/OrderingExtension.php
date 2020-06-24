<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Ordering\ServiceContainer;

use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Behat\Testwork\Tester\ServiceContainer\TesterExtension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Provides specification execution ordering.
 *
 * @author Ciaran McNulty <mail@ciaranmcnulty.com>
 */
final class OrderingExtension implements Extension
{
    public const ORDERER_TAG = 'tester.orderer';

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
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(CliExtension::CONTROLLER_TAG . '.order');
        $references = $this->processor->findAndSortTaggedServices($container, self::ORDERER_TAG);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerOrderer', array($reference));
        }
    }

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'ordering';
    }

    /**
     * Initializes other extensions.
     *
     * This method is called immediately after all extensions are activated but
     * before any extension `configure()` method is called. This allows extensions
     * to hook into the configuration of other extensions providing such an
     * extension point.
     *
     * @param ExtensionManager $extensionManager
     */
    public function initialize(ExtensionManager $extensionManager)
    {
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
     * @param array $config
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadOrderController($container);
        $this->loadOrderedExercise($container);
        $this->loadDefaultOrderers($container);
    }

    /**
     * Loads order controller.
     *
     * @param ContainerBuilder $container
     */
    private function loadOrderController(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Ordering\Cli\OrderController', array(
            new Reference(EventDispatcherExtension::DISPATCHER_ID),
            new Reference(TesterExtension::EXERCISE_WRAPPER_TAG . '.ordering')
        ));
        $definition->addTag(CliExtension::CONTROLLER_TAG, array('priority' => 250));
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.order', $definition);
    }

    /**
     * Loads exercise wrapper that enables ordering
     *
     * @param ContainerBuilder $container
     */
    private function loadOrderedExercise(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Ordering\OrderedExercise', array(
            new Reference(TesterExtension::EXERCISE_ID)
        ));
        $definition->addTag(TesterExtension::EXERCISE_WRAPPER_TAG, array('priority' => -9999));
        $container->setDefinition(TesterExtension::EXERCISE_WRAPPER_TAG . '.ordering', $definition);
    }

    /**
     * Defines default orderers
     *
     * @param ContainerBuilder $container
     */
    private function loadDefaultOrderers(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Ordering\Orderer\ReverseOrderer');
        $definition->addTag(self::ORDERER_TAG, array('priority' => -9999));
        $container->setDefinition(TesterExtension::EXERCISE_WRAPPER_TAG . '.ordering.reverse', $definition);


        $definition = new Definition('Behat\Testwork\Ordering\Orderer\RandomOrderer');
        $definition->addTag(self::ORDERER_TAG, array('priority' => -9999));
        $container->setDefinition(TesterExtension::EXERCISE_WRAPPER_TAG . '.ordering.random', $definition);
    }
}

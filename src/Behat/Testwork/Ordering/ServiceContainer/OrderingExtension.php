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
use Behat\Testwork\Ordering\Cli\OrderController;
use Behat\Testwork\Ordering\OrderedExercise;
use Behat\Testwork\Ordering\Orderer\RandomOrderer;
use Behat\Testwork\Ordering\Orderer\ReverseOrderer;
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
     */
    public function __construct(?ServiceProcessor $processor = null)
    {
        $this->processor = $processor ?: new ServiceProcessor();
    }

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @api
     */
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(CliExtension::CONTROLLER_TAG . '.order');
        $references = $this->processor->findAndSortTaggedServices($container, self::ORDERER_TAG);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerOrderer', [$reference]);
        }
    }

    /**
     * Returns the extension config key.
     */
    public function getConfigKey(): string
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
     */
    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    /**
     * Setups configuration for the extension.
     */
    public function configure(ArrayNodeDefinition $builder): void
    {
    }

    /**
     * Loads extension services into temporary container.
     */
    public function load(ContainerBuilder $container, array $config): void
    {
        $this->loadOrderController($container);
        $this->loadOrderedExercise($container);
        $this->loadDefaultOrderers($container);
    }

    /**
     * Loads order controller.
     */
    private function loadOrderController(ContainerBuilder $container): void
    {
        $definition = new Definition(OrderController::class, [
            new Reference(EventDispatcherExtension::DISPATCHER_ID),
            new Reference(TesterExtension::EXERCISE_WRAPPER_TAG . '.ordering'),
        ]);
        $definition->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 250]);
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.order', $definition);
    }

    /**
     * Loads exercise wrapper that enables ordering.
     */
    private function loadOrderedExercise(ContainerBuilder $container): void
    {
        $definition = new Definition(OrderedExercise::class, [
            new Reference(TesterExtension::EXERCISE_ID),
        ]);
        $definition->addTag(TesterExtension::EXERCISE_WRAPPER_TAG, ['priority' => -9999]);
        $container->setDefinition(TesterExtension::EXERCISE_WRAPPER_TAG . '.ordering', $definition);
    }

    /**
     * Defines default orderers.
     */
    private function loadDefaultOrderers(ContainerBuilder $container): void
    {
        $definition = new Definition(ReverseOrderer::class);
        $definition->addTag(self::ORDERER_TAG, ['priority' => -9999]);
        $container->setDefinition(TesterExtension::EXERCISE_WRAPPER_TAG . '.ordering.reverse', $definition);

        $definition = new Definition(RandomOrderer::class);
        $definition->addTag(self::ORDERER_TAG, ['priority' => -9999]);
        $container->setDefinition(TesterExtension::EXERCISE_WRAPPER_TAG . '.ordering.random', $definition);
    }
}

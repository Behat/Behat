<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call\ServiceContainer;

use Behat\Testwork\Call\CallCenter;
use Behat\Testwork\Call\Handler\RuntimeCallHandler;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Provides call services for testwork.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class CallExtension implements Extension
{
    /*
     * Available services
     */
    public const CALL_CENTER_ID = 'call.center';

    /*
     * Available extension points
     */
    public const CALL_FILTER_TAG = 'call.call_filter';
    public const CALL_HANDLER_TAG = 'call.call_handler';
    public const RESULT_FILTER_TAG = 'call.result_filter';
    public const EXCEPTION_HANDLER_TAG = 'call.exception_handler';

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

    public function getConfigKey(): string
    {
        return 'calls';
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('error_reporting')
                    ->info('Call executor will catch exceptions matching this level')
                    ->defaultValue(error_reporting())
        ;
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->loadCallCenter($container);
        $this->loadCallHandlers($container, $config['error_reporting']);
    }

    public function process(ContainerBuilder $container): void
    {
        $this->processCallFilters($container);
        $this->processCallHandlers($container);
        $this->processResultFilters($container);
        $this->processExceptionHandlers($container);
    }

    /**
     * Loads call center service.
     */
    protected function loadCallCenter(ContainerBuilder $container): void
    {
        $definition = new Definition(CallCenter::class);
        $container->setDefinition(self::CALL_CENTER_ID, $definition);
    }

    /**
     * Loads prebuilt call handlers.
     *
     * @param int $errorReporting
     */
    protected function loadCallHandlers(ContainerBuilder $container, $errorReporting): void
    {
        $definition = new Definition(RuntimeCallHandler::class, [$errorReporting]);
        $definition->addTag(self::CALL_HANDLER_TAG, ['priority' => 50]);
        $container->setDefinition(self::CALL_HANDLER_TAG . '.runtime', $definition);
    }

    /**
     * Registers all call filters to the CallCenter.
     */
    protected function processCallFilters(ContainerBuilder $container): void
    {
        $references = $this->processor->findAndSortTaggedServices($container, CallExtension::CALL_FILTER_TAG);
        $definition = $container->getDefinition(CallExtension::CALL_CENTER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerCallFilter', [$reference]);
        }
    }

    /**
     * Registers all call handlers to the CallCenter.
     */
    protected function processCallHandlers(ContainerBuilder $container): void
    {
        $references = $this->processor->findAndSortTaggedServices($container, CallExtension::CALL_HANDLER_TAG);
        $definition = $container->getDefinition(CallExtension::CALL_CENTER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerCallHandler', [$reference]);
        }
    }

    /**
     * Registers all call result filters to the CallCenter.
     */
    protected function processResultFilters(ContainerBuilder $container): void
    {
        $references = $this->processor->findAndSortTaggedServices($container, CallExtension::RESULT_FILTER_TAG);
        $definition = $container->getDefinition(CallExtension::CALL_CENTER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerResultFilter', [$reference]);
        }
    }

    /**
     * Registers all exception handlers to the CallCenter.
     */
    private function processExceptionHandlers(ContainerBuilder $container): void
    {
        $references = $this->processor->findAndSortTaggedServices($container, CallExtension::EXCEPTION_HANDLER_TAG);
        $definition = $container->getDefinition(CallExtension::CALL_CENTER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerExceptionHandler', [$reference]);
        }
    }
}

<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call\ServiceContainer;

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
    const CALL_CENTER_ID = 'call.center';

    /*
     * Available extension points
     */
    const CALL_FILTER_TAG = 'call.call_filter';
    const CALL_HANDLER_TAG = 'call.call_handler';
    const RESULT_FILTER_TAG = 'call.result_filter';

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
        $this->processor = $processor ? : new ServiceProcessor();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return 'calls';
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
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('error_reporting')
                    ->info('Call executor will catch exceptions matching this level')
                    ->defaultValue(E_ALL | E_STRICT)
                ->end()
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadCallCenter($container);
        $this->loadCallHandlers($container, $config['error_reporting']);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->processCallFilters($container);
        $this->processCallHandlers($container);
        $this->processResultFilters($container);
    }

    /**
     * Loads call center service.
     *
     * @param ContainerBuilder $container
     */
    protected function loadCallCenter(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Call\CallCenter');
        $container->setDefinition(self::CALL_CENTER_ID, $definition);
    }

    /**
     * Loads prebuilt call handlers.
     *
     * @param ContainerBuilder $container
     * @param integer          $errorReporting
     */
    protected function loadCallHandlers(ContainerBuilder $container, $errorReporting)
    {
        $definition = new Definition('Behat\Testwork\Call\Handler\RuntimeCallHandler', array($errorReporting));
        $definition->addTag(self::CALL_HANDLER_TAG, array('priority' => 50));
        $container->setDefinition(self::CALL_HANDLER_TAG . '.runtime', $definition);
    }

    /**
     * Registers all call filters to the CallCenter.
     *
     * @param ContainerBuilder $container
     */
    protected function processCallFilters(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, CallExtension::CALL_FILTER_TAG);
        $definition = $container->getDefinition(CallExtension::CALL_CENTER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerCallFilter', array($reference));
        }
    }

    /**
     * Registers all call handlers to the CallCenter.
     *
     * @param ContainerBuilder $container
     */
    protected function processCallHandlers(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, CallExtension::CALL_HANDLER_TAG);
        $definition = $container->getDefinition(CallExtension::CALL_CENTER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerCallHandler', array($reference));
        }
    }

    /**
     * Registers all call result filters to the CallCenter.
     *
     * @param ContainerBuilder $container
     */
    protected function processResultFilters(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, CallExtension::RESULT_FILTER_TAG);
        $definition = $container->getDefinition(CallExtension::CALL_CENTER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerResultFilter', array($reference));
        }
    }
}

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
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Testwork call extension.
 *
 * Provides call services for testwork.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class CallExtension implements Extension
{
    /*
     * Available services
     */
    const CALL_CENTRE_ID = 'call.centre';

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
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'call';
    }

    /**
     * Setups configuration for the extension.
     *
     * @param ArrayNodeDefinition $builder
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('error_reporting')->defaultValue(E_ALL)
            ->end()
        ;
    }

    /**
     * Loads extension services into temporary container.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadCallCentre($container);
        $this->loadCallHandlers($container, $config['error_reporting']);
    }

    /**
     * Processes shared container after all extensions loaded.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $this->processCallFilters($container);
        $this->processCallHandlers($container);
        $this->processResultFilters($container);
    }

    /**
     * Loads call centre service.
     *
     * @param ContainerBuilder $container
     */
    protected function loadCallCentre(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Call\CallCentre');
        $container->setDefinition(self::CALL_CENTRE_ID, $definition);
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
     * Registers all call filters to the CallCentre.
     *
     * @param ContainerBuilder $container
     */
    protected function processCallFilters(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, CallExtension::CALL_FILTER_TAG);
        $definition = $container->getDefinition(CallExtension::CALL_CENTRE_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerCallFilter', array($reference));
        }
    }

    /**
     * Registers all call handlers to the CallCentre.
     *
     * @param ContainerBuilder $container
     */
    protected function processCallHandlers(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, CallExtension::CALL_HANDLER_TAG);
        $definition = $container->getDefinition(CallExtension::CALL_CENTRE_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerCallHandler', array($reference));
        }
    }

    /**
     * Registers all call result filters to the CallCentre.
     *
     * @param ContainerBuilder $container
     */
    protected function processResultFilters(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, CallExtension::RESULT_FILTER_TAG);
        $definition = $container->getDefinition(CallExtension::CALL_CENTRE_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerResultFilter', array($reference));
        }
    }
}

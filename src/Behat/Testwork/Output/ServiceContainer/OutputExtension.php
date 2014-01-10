<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\ServiceContainer;

use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Testwork output extension.
 *
 * Provides output management services for testwork.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class OutputExtension implements Extension
{
    /*
     * Available services
     */
    const MANAGER_ID = 'output.manager';

    /*
     * Available extension points
     */
    const FORMATTER_TAG = 'output.formatter';

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
        return 'formatters';
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
            ->defaultValue(array($this->getDefaultFormatterName() => array('enabled' => true)))
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->beforeNormalization()
                    ->ifTrue(function($a) {
                        return is_array($a) && !isset($a['enabled']);
                    })
                    ->then(function($a) { return
                        array_merge($a, array('enabled' => true));
                    })
                ->end()
                ->useAttributeAsKey('name')
                ->treatTrueLike(array('enabled' => true))
                ->treatNullLike(array('enabled' => true))
                ->treatFalseLike(array('enabled' => false))
                ->prototype('variable')->end()
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadOutputController($container);
        $this->loadFormatters($container);
        $this->loadManager($container, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->processFormatters($container);
    }

    /**
     * Loads output controller.
     *
     * @param ContainerBuilder $container
     */
    protected function loadOutputController(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Output\Cli\OutputController', array(
            new Reference(self::MANAGER_ID)
        ));
        $definition->addTag(CliExtension::CONTROLLER_TAG, array('priority' => 1000));
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.output', $definition);
    }

    /**
     * Loads output manager.
     *
     * @param ContainerBuilder $container
     * @param array            $formatters
     */
    protected function loadManager(ContainerBuilder $container, array $formatters)
    {
        $definition = new Definition('Behat\Testwork\Output\OutputManager', array(
            new Reference(EventDispatcherExtension::DISPATCHER_ID)
        ));

        foreach ($formatters as $name => $parameters) {
            if ($parameters['enabled']) {
                $definition->addMethodCall('enableFormatter', array($name));
            } else {
                $definition->addMethodCall('disableFormatter', array($name));
            }

            unset($parameters['enabled']);
            $definition->addMethodCall('setFormatterParameters', array($name, $parameters));
        }

        $container->setDefinition(self::MANAGER_ID, $definition);
    }

    /**
     * Loads default formatters.
     *
     * @param ContainerBuilder $container
     */
    abstract protected function loadFormatters(ContainerBuilder $container);

    /**
     * Returns default formatter name.
     *
     * @return string
     */
    abstract protected function getDefaultFormatterName();

    /**
     * Creates output printer definition.
     *
     * @return Definition
     */
    protected function createOutputPrinterDefinition()
    {
        return new Definition('Behat\Testwork\Output\Printer\ConsoleOutputPrinter');
    }

    /**
     * Processes all available output formatters.
     *
     * @param ContainerBuilder $container
     */
    protected function processFormatters(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::FORMATTER_TAG);
        $definition = $container->getDefinition(self::MANAGER_ID);

        $previousCalls = $definition->getMethodCalls();
        $definition->setMethodCalls();

        foreach ($references as $reference) {
            $definition->addMethodCall('registerFormatter', array($reference));
        }
        foreach ($previousCalls as $call) {
            $definition->addMethodCall($call[0], $call[1]);
        }
    }
}

<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Exception\ServiceContainer;

use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Provides exception handling services.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ExceptionExtension implements Extension
{
    /*
     * Available services
     */
    public const PRESENTER_ID = 'exception.presenter';

    /*
     * Available extension points
     */
    public const STRINGER_TAG = 'exception.stringer';

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
        return 'exceptions';
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
                ->scalarNode('verbosity')
                    ->info('Output verbosity')
                    ->example(sprintf('%d, %d, %d, %d',
                        OutputPrinter::VERBOSITY_NORMAL,
                        OutputPrinter::VERBOSITY_VERBOSE,
                        OutputPrinter::VERBOSITY_VERY_VERBOSE,
                        OutputPrinter::VERBOSITY_DEBUG
                    ))
                    ->defaultValue(OutputPrinter::VERBOSITY_NORMAL)
                ->end()
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadPresenter($container, $config['verbosity']);
        $this->loadDefaultStringers($container);
        $this->loadVerbosityController($container);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->processStringers($container);
    }

    /**
     * Loads exception presenter.
     *
     * @param ContainerBuilder $container
     * @param integer          $verbosity
     */
    protected function loadPresenter(ContainerBuilder $container, $verbosity)
    {
        $definition = new Definition('Behat\Testwork\Exception\ExceptionPresenter', array(
            '%paths.base%',
            $verbosity
        ));
        $container->setDefinition(self::PRESENTER_ID, $definition);
    }

    /**
     * Loads default stringer.
     *
     * @param ContainerBuilder $container
     */
    protected function loadDefaultStringers(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Exception\Stringer\PHPUnitExceptionStringer');
        $definition->addTag(self::STRINGER_TAG, array('priority' => 50));
        $container->setDefinition(self::STRINGER_TAG . '.phpunit', $definition);

        $definition = new Definition('Behat\Testwork\Exception\Stringer\TestworkExceptionStringer');
        $definition->addTag(self::STRINGER_TAG, array('priority' => 50));
        $container->setDefinition(self::STRINGER_TAG . '.testwork', $definition);
    }

    /**
     * Processes all available exception stringers.
     *
     * @param ContainerBuilder $container
     */
    protected function processStringers(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::STRINGER_TAG);
        $definition = $container->getDefinition(self::PRESENTER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerExceptionStringer', array($reference));
        }
    }

    /**
     * Loads verbosity controller.
     *
     * @param ContainerBuilder $container
     */
    protected function loadVerbosityController($container)
    {
        $definition = new Definition('Behat\Testwork\Exception\Cli\VerbosityController', array(
            new Reference(self::PRESENTER_ID)
        ));
        $definition->addTag(CliExtension::CONTROLLER_TAG, array('priority' => 9999));
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.exception_verbosity', $definition);
    }
}

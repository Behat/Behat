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
use Behat\Testwork\Exception\Cli\VerbosityController;
use Behat\Testwork\Exception\ExceptionPresenter;
use Behat\Testwork\Exception\Stringer\PHPUnitExceptionStringer;
use Behat\Testwork\Exception\Stringer\TestworkExceptionStringer;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\PathOptions\ServiceContainer\PathOptionsExtension;
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

    private readonly ServiceProcessor $processor;

    /**
     * Initializes extension.
     */
    public function __construct(?ServiceProcessor $processor = null)
    {
        $this->processor = $processor ?: new ServiceProcessor();
    }

    public function getConfigKey(): string
    {
        return 'exceptions';
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('verbosity')
                    ->info('Output verbosity')
                    ->example(sprintf(
                        '%d, %d, %d, %d',
                        OutputPrinter::VERBOSITY_NORMAL,
                        OutputPrinter::VERBOSITY_VERBOSE,
                        OutputPrinter::VERBOSITY_VERY_VERBOSE,
                        OutputPrinter::VERBOSITY_DEBUG
                    ))
                    ->defaultValue(OutputPrinter::VERBOSITY_NORMAL)
        ;
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->loadPresenter($container, $config['verbosity']);
        $this->loadDefaultStringers($container);
        $this->loadVerbosityController($container);
    }

    public function process(ContainerBuilder $container): void
    {
        $this->processStringers($container);
    }

    /**
     * Loads exception presenter.
     *
     * @param int $verbosity
     */
    private function loadPresenter(ContainerBuilder $container, $verbosity): void
    {
        $definition = new Definition(ExceptionPresenter::class, [
            '%paths.base%',
            $verbosity,
            new Reference(PathOptionsExtension::CONFIGURABLE_PATH_PRINTER_ID),
        ]);
        $container->setDefinition(self::PRESENTER_ID, $definition);
    }

    /**
     * Loads default stringer.
     */
    private function loadDefaultStringers(ContainerBuilder $container): void
    {
        $definition = new Definition(PHPUnitExceptionStringer::class);
        $definition->addTag(self::STRINGER_TAG, ['priority' => 50]);
        $container->setDefinition(self::STRINGER_TAG . '.phpunit', $definition);

        $definition = new Definition(TestworkExceptionStringer::class);
        $definition->addTag(self::STRINGER_TAG, ['priority' => 50]);
        $container->setDefinition(self::STRINGER_TAG . '.testwork', $definition);
    }

    /**
     * Processes all available exception stringers.
     */
    private function processStringers(ContainerBuilder $container): void
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::STRINGER_TAG);
        $definition = $container->getDefinition(self::PRESENTER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerExceptionStringer', [$reference]);
        }
    }

    /**
     * Loads verbosity controller.
     *
     * @param ContainerBuilder $container
     */
    private function loadVerbosityController($container): void
    {
        $definition = new Definition(VerbosityController::class, [
            new Reference(self::PRESENTER_ID),
        ]);
        $definition->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 9999]);
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.exception_verbosity', $definition);
    }
}

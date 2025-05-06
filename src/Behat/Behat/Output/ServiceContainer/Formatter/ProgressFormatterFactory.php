<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\ServiceContainer\Formatter;

use Behat\Config\Formatter\ProgressFormatter;
use Behat\Testwork\Exception\ServiceContainer\ExceptionExtension;
use Behat\Testwork\Output\ServiceContainer\Formatter\FormatterFactory;
use Behat\Testwork\Output\ServiceContainer\OutputExtension;
use Behat\Testwork\PathOptions\ServiceContainer\PathOptionsExtension;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Behat\Testwork\Translator\ServiceContainer\TranslatorExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Behat progress formatter factory.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ProgressFormatterFactory implements FormatterFactory
{
    /**
     * @var ServiceProcessor
     */
    private $processor;

    /*
     * Available services
     */
    public const ROOT_LISTENER_ID = 'output.node.listener.progress';
    public const RESULT_TO_STRING_CONVERTER_ID = 'output.node.printer.result_to_string';

    /*
     * Available extension points
     */
    public const ROOT_LISTENER_WRAPPER_TAG = 'output.node.listener.progress.wrapper';

    /**
     * Initializes extension.
     */
    public function __construct(?ServiceProcessor $processor = null)
    {
        $this->processor = $processor ?: new ServiceProcessor();
    }

    public function buildFormatter(ContainerBuilder $container)
    {
        $this->loadRootNodeListener($container);
        $this->loadCorePrinters($container);
        $this->loadPrinterHelpers($container);
        $this->loadFormatter($container);
    }

    public function processFormatter(ContainerBuilder $container)
    {
        $this->processListenerWrappers($container);
    }

    /**
     * Loads progress formatter node event listener.
     */
    protected function loadRootNodeListener(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\EventListener\AST\StepListener', [
            new Reference('output.node.printer.progress.step'),
        ]);
        $container->setDefinition(self::ROOT_LISTENER_ID, $definition);
    }

    /**
     * Loads feature, scenario and step printers.
     */
    protected function loadCorePrinters(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\Printer\CounterPrinter', [
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference(TranslatorExtension::TRANSLATOR_ID),
        ]);
        $container->setDefinition('output.node.printer.counter', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\ListPrinter', [
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference(ExceptionExtension::PRESENTER_ID),
            new Reference(TranslatorExtension::TRANSLATOR_ID),
            '%paths.base%',
            new Reference(PathOptionsExtension::CONFIGURABLE_PATH_PRINTER_ID),
        ]);
        $container->setDefinition('output.node.printer.list', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Progress\ProgressStepPrinter', [
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
        ]);
        $container->setDefinition('output.node.printer.progress.step', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Progress\ProgressStatisticsPrinter', [
            new Reference('output.node.printer.counter'),
            new Reference('output.node.printer.list'),
        ]);
        $container->setDefinition('output.node.printer.progress.statistics', $definition);
    }

    /**
     * Loads printer helpers.
     */
    protected function loadPrinterHelpers(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter');
        $container->setDefinition(self::RESULT_TO_STRING_CONVERTER_ID, $definition);
    }

    /**
     * Loads formatter itself.
     */
    protected function loadFormatter(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Statistics\TotalStatistics');
        $container->setDefinition('output.progress.statistics', $definition);

        $definition = new Definition('Behat\Testwork\Output\NodeEventListeningFormatter', [
            ProgressFormatter::NAME,
            'Prints one character per step.',
            ProgressFormatter::defaults(),
            $this->createOutputPrinterDefinition(),
            new Definition(
                'Behat\Testwork\Output\Node\EventListener\ChainEventListener',
                [
                    [
                        new Reference(self::ROOT_LISTENER_ID),
                        new Definition('Behat\Behat\Output\Node\EventListener\Statistics\StatisticsListener', [
                            new Reference('output.progress.statistics'),
                            new Reference('output.node.printer.progress.statistics'),
                        ]),
                        new Definition('Behat\Behat\Output\Node\EventListener\Statistics\ScenarioStatsListener', [
                            new Reference('output.progress.statistics'),
                        ]),
                        new Definition('Behat\Behat\Output\Node\EventListener\Statistics\StepStatsListener', [
                            new Reference('output.progress.statistics'),
                            new Reference(ExceptionExtension::PRESENTER_ID),
                        ]),
                        new Definition('Behat\Behat\Output\Node\EventListener\Statistics\HookStatsListener', [
                            new Reference('output.progress.statistics'),
                            new Reference(ExceptionExtension::PRESENTER_ID),
                        ]),
                    ],
                ]
            ),
        ]);
        $definition->addTag(OutputExtension::FORMATTER_TAG, ['priority' => 100]);
        $container->setDefinition(OutputExtension::FORMATTER_TAG . '.progress', $definition);
    }

    /**
     * Creates output printer definition.
     *
     * @return Definition
     */
    protected function createOutputPrinterDefinition()
    {
        return new Definition('Behat\Testwork\Output\Printer\StreamOutputPrinter', [
            new Definition('Behat\Behat\Output\Printer\ConsoleOutputFactory'),
        ]);
    }

    /**
     * Processes all registered pretty formatter node listener wrappers.
     */
    protected function processListenerWrappers(ContainerBuilder $container)
    {
        $this->processor->processWrapperServices($container, self::ROOT_LISTENER_ID, self::ROOT_LISTENER_WRAPPER_TAG);
    }
}

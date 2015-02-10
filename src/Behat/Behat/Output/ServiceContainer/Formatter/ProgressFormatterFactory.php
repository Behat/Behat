<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\ServiceContainer\Formatter;

use Behat\Testwork\Exception\ServiceContainer\ExceptionExtension;
use Behat\Testwork\Output\ServiceContainer\Formatter\FormatterFactory;
use Behat\Testwork\Output\ServiceContainer\OutputExtension;
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
    const ROOT_LISTENER_ID = 'output.node.listener.progress';
    const RESULT_TO_STRING_CONVERTER_ID = 'output.node.printer.result_to_string';

    /*
     * Available extension points
     */
    const ROOT_LISTENER_WRAPPER_TAG = 'output.node.listener.progress.wrapper';

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
    public function buildFormatter(ContainerBuilder $container)
    {
        $this->loadRootNodeListener($container);
        $this->loadCorePrinters($container);
        $this->loadPrinterHelpers($container);
        $this->loadFormatter($container);
    }

    /**
     * {@inheritdoc}
     */
    public function processFormatter(ContainerBuilder $container)
    {
        $this->processListenerWrappers($container);
    }

    /**
     * Loads progress formatter node event listener.
     *
     * @param ContainerBuilder $container
     */
    protected function loadRootNodeListener(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\EventListener\AST\StepListener', array(
            new Reference('output.node.printer.progress.step')
        ));
        $container->setDefinition(self::ROOT_LISTENER_ID, $definition);
    }

    /**
     * Loads feature, scenario and step printers.
     *
     * @param ContainerBuilder $container
     */
    protected function loadCorePrinters(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\Printer\CounterPrinter', array(
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference(TranslatorExtension::TRANSLATOR_ID),
        ));
        $container->setDefinition('output.node.printer.counter', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\ListPrinter', array(
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference(ExceptionExtension::PRESENTER_ID),
            new Reference(TranslatorExtension::TRANSLATOR_ID),
            '%paths.base%'
        ));
        $container->setDefinition('output.node.printer.list', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Progress\ProgressStepPrinter', array(
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID)
        ));
        $container->setDefinition('output.node.printer.progress.step', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Progress\ProgressStatisticsPrinter', array(
            new Reference('output.node.printer.counter'),
            new Reference('output.node.printer.list')
        ));
        $container->setDefinition('output.node.printer.progress.statistics', $definition);
    }

    /**
     * Loads printer helpers.
     *
     * @param ContainerBuilder $container
     */
    protected function loadPrinterHelpers(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter');
        $container->setDefinition(self::RESULT_TO_STRING_CONVERTER_ID, $definition);
    }

    /**
     * Loads formatter itself.
     *
     * @param ContainerBuilder $container
     */
    protected function loadFormatter(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Statistics\TotalStatistics');
        $container->setDefinition('output.progress.statistics', $definition);

        $definition = new Definition('Behat\Testwork\Output\NodeEventListeningFormatter', array(
            'progress',
            'Prints one character per step.',
            array(
                'timer' => true
            ),
            $this->createOutputPrinterDefinition(),
            new Definition('Behat\Testwork\Output\Node\EventListener\ChainEventListener', array(
                    array(
                        new Reference(self::ROOT_LISTENER_ID),
                        new Definition('Behat\Behat\Output\Node\EventListener\Statistics\StatisticsListener', array(
                            new Reference('output.progress.statistics'),
                            new Reference('output.node.printer.progress.statistics')
                        )),
                        new Definition('Behat\Behat\Output\Node\EventListener\Statistics\ScenarioStatsListener', array(
                            new Reference('output.progress.statistics')
                        )),
                        new Definition('Behat\Behat\Output\Node\EventListener\Statistics\StepStatsListener', array(
                            new Reference('output.progress.statistics'),
                            new Reference(ExceptionExtension::PRESENTER_ID)
                        )),
                        new Definition('Behat\Behat\Output\Node\EventListener\Statistics\HookStatsListener', array(
                            new Reference('output.progress.statistics'),
                            new Reference(ExceptionExtension::PRESENTER_ID)
                        )),
                    )
                )
            )
        ));
        $definition->addTag(OutputExtension::FORMATTER_TAG, array('priority' => 100));
        $container->setDefinition(OutputExtension::FORMATTER_TAG . '.progress', $definition);
    }

    /**
     * Creates output printer definition.
     *
     * @return Definition
     */
    protected function createOutputPrinterDefinition()
    {
        return new Definition('Behat\Testwork\Output\Printer\StreamOutputPrinter', array(
            new Definition('Behat\Behat\Output\Printer\ConsoleOutputFactory'),
        ));
    }

    /**
     * Processes all registered pretty formatter node listener wrappers.
     *
     * @param ContainerBuilder $container
     */
    protected function processListenerWrappers(ContainerBuilder $container)
    {
        $this->processor->processWrapperServices($container, self::ROOT_LISTENER_ID, self::ROOT_LISTENER_WRAPPER_TAG);
    }
}

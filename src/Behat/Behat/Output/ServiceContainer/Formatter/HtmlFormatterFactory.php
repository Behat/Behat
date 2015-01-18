<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\ServiceContainer\Formatter;

use Behat\Behat\Definition\ServiceContainer\DefinitionExtension;
use Behat\Behat\EventDispatcher\Event\BackgroundTested;
use Behat\Behat\EventDispatcher\Event\OutlineTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Testwork\Exception\ServiceContainer\ExceptionExtension;
use Behat\Testwork\Output\ServiceContainer\Formatter\FormatterFactory;
use Behat\Testwork\Output\ServiceContainer\OutputExtension;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Behat\Testwork\Translator\ServiceContainer\TranslatorExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Behat html formatter factory.
 *
 * @author Ali Bahman <abn@webit4.me>
 */
class HtmlFormatterFactory implements FormatterFactory
{
    /**
     * @var ServiceProcessor
     */
    private $processor;

    /*
     * Available services
     */
    const ROOT_LISTENER_ID = 'output.node.listener.html';
    const RESULT_TO_STRING_CONVERTER_ID = 'output.node.printer.result_to_string';
    const HTML_PRINTER_ID = 'output.node.printer.helper';

    /*
     * Available extension points
     */
    const ROOT_LISTENER_WRAPPER_TAG = 'output.node.listener.html.wrapper';

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
        $this->loadTableOutlinePrinter($container);
        $this->loadExpandedOutlinePrinter($container);
        $this->loadHookPrinters($container);
        $this->loadStatisticsPrinter($container);
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
     * Loads html formatter node event listener.
     *
     * @param ContainerBuilder $container
     */
    protected function loadRootNodeListener(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Output\Node\EventListener\ChainEventListener', array(
            array(
                new Definition('Behat\Behat\Output\Node\EventListener\AST\SuiteListener',
                    array(
                        new Reference('output.node.printer.html.Suite_setup')
                    )
                ),
                new Definition('Behat\Behat\Output\Node\EventListener\AST\FeatureListener', array(
                    new Reference('output.node.printer.html.feature'),
                    new Reference('output.node.printer.html.feature_setup')
                )),
                $this->proxySiblingEvents(
                    BackgroundTested::BEFORE,
                    BackgroundTested::AFTER,
                    array(
                        new Definition('Behat\Behat\Output\Node\EventListener\AST\ScenarioNodeListener', array(
                            BackgroundTested::AFTER_SETUP,
                            BackgroundTested::AFTER,
                            new Reference('output.node.printer.html.scenario')
                        )),
                        new Definition('Behat\Behat\Output\Node\EventListener\AST\StepListener', array(
                            new Reference('output.node.printer.html.step'),
                            new Reference('output.node.printer.html.step_setup')
                        )),
                    )
                ),
                $this->proxySiblingEvents(
                    ScenarioTested::BEFORE,
                    ScenarioTested::AFTER,
                    array(
                        new Definition('Behat\Behat\Output\Node\EventListener\AST\ScenarioNodeListener', array(
                            ScenarioTested::AFTER_SETUP,
                            ScenarioTested::AFTER,
                            new Reference('output.node.printer.html.scenario'),
                            new Reference('output.node.printer.html.scenario_setup')
                        )),
                        new Definition('Behat\Behat\Output\Node\EventListener\AST\StepListener', array(
                            new Reference('output.node.printer.html.step'),
                            new Reference('output.node.printer.html.step_setup')
                        )),
                    )
                ),
                $this->proxySiblingEvents(
                    OutlineTested::BEFORE,
                    OutlineTested::AFTER,
                    array(
                        $this->proxyEventsIfParameterIsSet(
                            'expand',
                            false,
                            new Definition('Behat\Behat\Output\Node\EventListener\AST\OutlineTableListener', array(
                                new Reference('output.node.printer.html.outline_table'),
                                new Reference('output.node.printer.html.example_row'),
                                new Reference('output.node.printer.html.example_setup'),
                                new Reference('output.node.printer.html.example_step_setup')
                            ))
                        ),
                        $this->proxyEventsIfParameterIsSet(
                            'expand',
                            true,
                            new Definition('Behat\Behat\Output\Node\EventListener\AST\OutlineListener', array(
                                new Reference('output.node.printer.html.outline'),
                                new Reference('output.node.printer.html.example'),
                                new Reference('output.node.printer.html.example_step'),
                                new Reference('output.node.printer.html.example_setup'),
                                new Reference('output.node.printer.html.example_step_setup')
                            ))
                        )
                    )
                ),
            )
        ));
        $container->setDefinition(self::ROOT_LISTENER_ID, $definition);
    }

    /**
     * Loads formatter itself.
     *
     * @param ContainerBuilder $container
     */
    protected function loadFormatter(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Statistics\Statistics');
        $container->setDefinition('output.html.statistics', $definition);

        $definition = new Definition('Behat\Testwork\Output\NodeEventListeningFormatter', array(
            'html',
            'Prints the feature as is.',
            array(
                'timer'     => true,
                'expand'    => false,
                'paths'     => true,
                'multiline' => true,
            ),
            $this->createOutputPrinterDefinition(),
            new Definition('Behat\Testwork\Output\Node\EventListener\ChainEventListener', array(
                    array(
                        $this->rearrangeBackgroundEvents(
                            new Reference(self::ROOT_LISTENER_ID)
                        ),
                        new Definition('Behat\Behat\Output\Node\EventListener\Statistics\StatisticsListener', array(
                            new Reference('output.html.statistics'),
                            new Reference('output.node.printer.html.statistics')
                        )),
                        new Definition('Behat\Behat\Output\Node\EventListener\Statistics\ScenarioStatsListener', array(
                            new Reference('output.html.statistics')
                        )),
                        new Definition('Behat\Behat\Output\Node\EventListener\Statistics\StepStatsListener', array(
                            new Reference('output.html.statistics'),
                            new Reference(ExceptionExtension::PRESENTER_ID)
                        )),
                    )
                )
            )
        ));
        $definition->addTag(OutputExtension::FORMATTER_TAG, array('priority' => 100));
        $container->setDefinition(OutputExtension::FORMATTER_TAG . '.html', $definition);
    }

    /**
     * Loads feature, scenario and step printers.
     *
     * @param ContainerBuilder $container
     */
    protected function loadCorePrinters(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\Printer\Html\HtmlFeaturePrinter',array(
            new Reference(self::HTML_PRINTER_ID),
        ));
        $container->setDefinition('output.node.printer.html.feature', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Html\HtmlScenarioPrinter', array(
            new Reference(self::HTML_PRINTER_ID),
        ));
        $container->setDefinition('output.node.printer.html.scenario', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Html\HtmlStepPrinter', array(
            new Reference(self::HTML_PRINTER_ID),
            new Reference(ExceptionExtension::PRESENTER_ID)
        ));
        $container->setDefinition('output.node.printer.html.step', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Html\HtmlSkippedStepPrinter', array(
            new Reference(self::HTML_PRINTER_ID),
        ));
        $container->setDefinition('output.node.printer.html.skipped_step', $definition);
    }

    /**
     * Loads table outline printer.
     *
     * @param ContainerBuilder $container
     */
    protected function loadTableOutlinePrinter(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\Printer\Html\HtmlOutlineTablePrinter', array(
            new Reference(self::HTML_PRINTER_ID),
            new Reference('output.node.printer.html.scenario'),
            new Reference('output.node.printer.html.skipped_step'),
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID)
        ));
        $container->setDefinition('output.node.printer.html.outline_table', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Html\HtmlExampleRowPrinter', array(
            new Reference(self::HTML_PRINTER_ID),
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference(ExceptionExtension::PRESENTER_ID)
        ));
        $container->setDefinition('output.node.printer.html.example_row', $definition);
    }

    /**
     * Loads expanded outline printer.
     *
     * @param ContainerBuilder $container
     */
    protected function loadExpandedOutlinePrinter(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\Printer\Html\HtmlOutlinePrinter', array(
            new Reference(self::HTML_PRINTER_ID),
            new Reference('output.node.printer.html.scenario'),
            new Reference('output.node.printer.html.skipped_step'),
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID)
        ));
        $container->setDefinition('output.node.printer.html.outline', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Html\HtmlExamplePrinter', array(
            new Reference(self::HTML_PRINTER_ID),
        ));
        $container->setDefinition('output.node.printer.html.example', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Html\HtmlStepPrinter', array(
            new Reference(self::HTML_PRINTER_ID),
            new Reference(ExceptionExtension::PRESENTER_ID)
        ));
        $container->setDefinition('output.node.printer.html.example_step', $definition);
    }

    /**
     * Loads hook printers.
     *
     * @param ContainerBuilder $container
     */
    protected function loadHookPrinters(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\Printer\Html\HtmlSuiteSetupPrinter',array(
            new Reference(self::HTML_PRINTER_ID)
        ));
        $container->setDefinition('output.node.printer.html.suite_setup',$definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Html\HtmlSetupPrinter', array(
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference(ExceptionExtension::PRESENTER_ID),
            0,
            false,
            true
        ));
        $container->setDefinition('output.node.printer.html.feature_setup', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Html\HtmlSetupPrinter', array(
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference(ExceptionExtension::PRESENTER_ID),
            2
        ));
        $container->setDefinition('output.node.printer.html.scenario_setup', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Html\HtmlSetupPrinter', array(
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference(ExceptionExtension::PRESENTER_ID),
            4
        ));
        $container->setDefinition('output.node.printer.html.step_setup', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Html\HtmlSetupPrinter', array(
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference(ExceptionExtension::PRESENTER_ID),
            8
        ));
        $container->setDefinition('output.node.printer.html.example_step_setup', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Html\HtmlSetupPrinter', array(
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference(ExceptionExtension::PRESENTER_ID),
            6
        ));
        $container->setDefinition('output.node.printer.html.example_setup', $definition);
    }

    /**
     * Loads statistics printer.
     *
     * @param ContainerBuilder $container
     */
    protected function loadStatisticsPrinter(ContainerBuilder $container)
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

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Html\HtmlStatisticsPrinter', array(
            new Reference(self::HTML_PRINTER_ID),
        ));
        $container->setDefinition('output.node.printer.html.statistics', $definition);
    }

    /**
     * Loads printer helpers.
     *
     * @param ContainerBuilder $container
     */
    protected function loadPrinterHelpers(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\Printer\Helper\WidthCalculator');
        $container->setDefinition('output.node.printer.html.width_calculator', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Helper\StepTextPainter', array(
            new Reference(DefinitionExtension::PATTERN_TRANSFORMER_ID),
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID)
        ));
        $container->setDefinition('output.node.printer.html.step_text_painter', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter');
        $container->setDefinition(self::RESULT_TO_STRING_CONVERTER_ID, $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Helper\HtmlPrinter');
        $container->setDefinition(self::HTML_PRINTER_ID, $definition);
    }

    /**
     * Creates output printer definition.
     *
     * @return Definition
     */
    protected function createOutputPrinterDefinition()
    {
        return new Definition('Behat\Behat\Output\Printer\ConsoleOutputPrinter');
    }

    /**
     * Creates root listener definition.
     *
     * @param mixed $listener
     *
     * @return Definition
     */
    protected function rearrangeBackgroundEvents($listener)
    {
        return new Definition('Behat\Behat\Output\Node\EventListener\Flow\FirstBackgroundFiresFirstListener', array(
            new Definition('Behat\Behat\Output\Node\EventListener\Flow\OnlyFirstBackgroundFiresListener', array(
                $listener
            ))
        ));
    }

    /**
     * Creates contextual proxy listener.
     *
     * @param string       $beforeEventName
     * @param string       $afterEventName
     * @param Definition[] $listeners
     *
     * @return Definition
     */
    protected function proxySiblingEvents($beforeEventName, $afterEventName, array $listeners)
    {
        return new Definition('Behat\Behat\Output\Node\EventListener\Flow\FireOnlySiblingsListener',
            array(
                $beforeEventName,
                $afterEventName,
                new Definition('Behat\Testwork\Output\Node\EventListener\ChainEventListener', array($listeners))
            )
        );
    }

    /**
     * Creates contextual proxy listener.
     *
     * @param string $name
     * @param mixed  $value
     * @param mixed  $listener
     *
     * @return Definition
     */
    protected function proxyEventsIfParameterIsSet($name, $value, Definition $listener)
    {
        return new Definition('Behat\Testwork\Output\Node\EventListener\Flow\FireOnlyIfFormatterParameterListener',
            array($name, $value, $listener)
        );
    }

    /**
     * Processes all registered html formatter node listener wrappers.
     *
     * @param ContainerBuilder $container
     */
    protected function processListenerWrappers(ContainerBuilder $container)
    {
        $this->processor->processWrapperServices($container, self::ROOT_LISTENER_ID, self::ROOT_LISTENER_WRAPPER_TAG);
    }
}

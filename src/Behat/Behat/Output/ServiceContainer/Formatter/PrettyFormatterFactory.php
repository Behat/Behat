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
use Behat\Config\Formatter\PrettyFormatter;
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
 * Behat pretty formatter factory.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PrettyFormatterFactory implements FormatterFactory
{
    /**
     * @var ServiceProcessor
     */
    private $processor;

    /*
     * Available services
     */
    public const ROOT_LISTENER_ID = 'output.node.listener.pretty';
    public const RESULT_TO_STRING_CONVERTER_ID = 'output.node.printer.result_to_string';

    /*
     * Available extension points
     */
    public const ROOT_LISTENER_WRAPPER_TAG = 'output.node.listener.pretty.wrapper';

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
        $this->loadTableOutlinePrinter($container);
        $this->loadExpandedOutlinePrinter($container);
        $this->loadHookPrinters($container);
        $this->loadStatisticsPrinter($container);
        $this->loadPrinterHelpers($container);

        $this->loadFormatter($container);
    }

    public function processFormatter(ContainerBuilder $container)
    {
        $this->processListenerWrappers($container);
    }

    /**
     * Loads pretty formatter node event listener.
     */
    protected function loadRootNodeListener(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Output\Node\EventListener\ChainEventListener', [
            [
                new Definition('Behat\Behat\Output\Node\EventListener\AST\SuiteListener', [
                    new Reference('output.node.printer.pretty.suite_setup'),
                ]),
                new Definition('Behat\Behat\Output\Node\EventListener\AST\FeatureListener', [
                    new Reference('output.node.printer.pretty.feature'),
                    new Reference('output.node.printer.pretty.feature_setup'),
                ]),
                $this->proxySiblingEvents(
                    BackgroundTested::BEFORE,
                    BackgroundTested::AFTER,
                    [
                        new Definition('Behat\Behat\Output\Node\EventListener\AST\ScenarioNodeListener', [
                            BackgroundTested::AFTER_SETUP,
                            BackgroundTested::AFTER,
                            new Reference('output.node.printer.pretty.scenario'),
                        ]),
                        new Definition('Behat\Behat\Output\Node\EventListener\AST\StepListener', [
                            new Reference('output.node.printer.pretty.step'),
                            new Reference('output.node.printer.pretty.step_setup'),
                        ]),
                    ]
                ),
                $this->proxySiblingEvents(
                    ScenarioTested::BEFORE,
                    ScenarioTested::AFTER,
                    [
                        new Definition('Behat\Behat\Output\Node\EventListener\AST\ScenarioNodeListener', [
                            ScenarioTested::AFTER_SETUP,
                            ScenarioTested::AFTER,
                            new Reference('output.node.printer.pretty.scenario'),
                            new Reference('output.node.printer.pretty.scenario_setup'),
                        ]),
                        new Definition('Behat\Behat\Output\Node\EventListener\AST\StepListener', [
                            new Reference('output.node.printer.pretty.step'),
                            new Reference('output.node.printer.pretty.step_setup'),
                        ]),
                    ]
                ),
                $this->proxySiblingEvents(
                    OutlineTested::BEFORE,
                    OutlineTested::AFTER,
                    [
                        $this->proxyEventsIfParameterIsSet(
                            'expand',
                            false,
                            new Definition('Behat\Behat\Output\Node\EventListener\AST\OutlineTableListener', [
                                new Reference('output.node.printer.pretty.outline_table'),
                                new Reference('output.node.printer.pretty.example_row'),
                                new Reference('output.node.printer.pretty.example_setup'),
                                new Reference('output.node.printer.pretty.example_step_setup'),
                            ])
                        ),
                        $this->proxyEventsIfParameterIsSet(
                            'expand',
                            true,
                            new Definition('Behat\Behat\Output\Node\EventListener\AST\OutlineListener', [
                                new Reference('output.node.printer.pretty.outline'),
                                new Reference('output.node.printer.pretty.example'),
                                new Reference('output.node.printer.pretty.example_step'),
                                new Reference('output.node.printer.pretty.example_setup'),
                                new Reference('output.node.printer.pretty.example_step_setup'),
                            ])
                        ),
                    ]
                ),
            ],
        ]);
        $container->setDefinition(self::ROOT_LISTENER_ID, $definition);
    }

    /**
     * Loads formatter itself.
     */
    protected function loadFormatter(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Statistics\TotalStatistics');
        $container->setDefinition('output.pretty.statistics', $definition);

        $definition = new Definition('Behat\Testwork\Output\NodeEventListeningFormatter', [
            PrettyFormatter::NAME,
            'Prints the feature as is.',
            PrettyFormatter::defaults(),
            $this->createOutputPrinterDefinition(),
            new Definition(
                'Behat\Testwork\Output\Node\EventListener\ChainEventListener',
                [
                    [
                        $this->rearrangeBackgroundEvents(
                            new Reference(self::ROOT_LISTENER_ID)
                        ),
                        new Definition('Behat\Behat\Output\Node\EventListener\Statistics\StatisticsListener', [
                            new Reference('output.pretty.statistics'),
                            new Reference('output.node.printer.pretty.statistics'),
                        ]),
                        new Definition('Behat\Behat\Output\Node\EventListener\Statistics\ScenarioStatsListener', [
                            new Reference('output.pretty.statistics'),
                        ]),
                        new Definition('Behat\Behat\Output\Node\EventListener\Statistics\StepStatsListener', [
                            new Reference('output.pretty.statistics'),
                            new Reference(ExceptionExtension::PRESENTER_ID),
                        ]),
                        new Definition('Behat\Behat\Output\Node\EventListener\Statistics\HookStatsListener', [
                            new Reference('output.pretty.statistics'),
                            new Reference(ExceptionExtension::PRESENTER_ID),
                        ]),
                    ],
                ]
            ),
        ]);
        $definition->addTag(OutputExtension::FORMATTER_TAG, ['priority' => 100]);
        $container->setDefinition(OutputExtension::FORMATTER_TAG . '.pretty', $definition);
    }

    /**
     * Loads feature, scenario and step printers.
     */
    protected function loadCorePrinters(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettyFeaturePrinter');
        $container->setDefinition('output.node.printer.pretty.feature', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettyPathPrinter', [
            new Reference('output.node.printer.pretty.width_calculator'),
            '%paths.base%',
            new Reference(PathOptionsExtension::CONFIGURABLE_PATH_PRINTER_ID),
        ]);
        $container->setDefinition('output.node.printer.pretty.path', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettyScenarioPrinter', [
            new Reference('output.node.printer.pretty.path'),
        ]);
        $container->setDefinition('output.node.printer.pretty.scenario', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettyStepPrinter', [
            new Reference('output.node.printer.pretty.step_text_painter'),
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference('output.node.printer.pretty.path'),
            new Reference(ExceptionExtension::PRESENTER_ID),
        ]);
        $container->setDefinition('output.node.printer.pretty.step', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettySkippedStepPrinter', [
            new Reference('output.node.printer.pretty.step_text_painter'),
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference('output.node.printer.pretty.path'),
        ]);
        $container->setDefinition('output.node.printer.pretty.skipped_step', $definition);
    }

    /**
     * Loads table outline printer.
     */
    protected function loadTableOutlinePrinter(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettyOutlineTablePrinter', [
            new Reference('output.node.printer.pretty.scenario'),
            new Reference('output.node.printer.pretty.skipped_step'),
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
        ]);
        $container->setDefinition('output.node.printer.pretty.outline_table', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettyExampleRowPrinter', [
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference(ExceptionExtension::PRESENTER_ID),
            new Reference(TranslatorExtension::TRANSLATOR_ID),
        ]);
        $container->setDefinition('output.node.printer.pretty.example_row', $definition);
    }

    /**
     * Loads expanded outline printer.
     */
    protected function loadExpandedOutlinePrinter(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettyOutlinePrinter', [
            new Reference('output.node.printer.pretty.scenario'),
            new Reference('output.node.printer.pretty.skipped_step'),
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
        ]);
        $container->setDefinition('output.node.printer.pretty.outline', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettyExamplePrinter', [
            new Reference('output.node.printer.pretty.path'),
        ]);
        $container->setDefinition('output.node.printer.pretty.example', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettyStepPrinter', [
            new Reference('output.node.printer.pretty.step_text_painter'),
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference('output.node.printer.pretty.path'),
            new Reference(ExceptionExtension::PRESENTER_ID),
            8,
        ]);
        $container->setDefinition('output.node.printer.pretty.example_step', $definition);
    }

    /**
     * Loads hook printers.
     */
    protected function loadHookPrinters(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettySetupPrinter', [
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference(ExceptionExtension::PRESENTER_ID),
            0,
            true,
            true,
        ]);
        $container->setDefinition('output.node.printer.pretty.suite_setup', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettySetupPrinter', [
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference(ExceptionExtension::PRESENTER_ID),
            0,
            false,
            true,
        ]);
        $container->setDefinition('output.node.printer.pretty.feature_setup', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettySetupPrinter', [
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference(ExceptionExtension::PRESENTER_ID),
            2,
        ]);
        $container->setDefinition('output.node.printer.pretty.scenario_setup', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettySetupPrinter', [
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference(ExceptionExtension::PRESENTER_ID),
            4,
        ]);
        $container->setDefinition('output.node.printer.pretty.step_setup', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettySetupPrinter', [
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference(ExceptionExtension::PRESENTER_ID),
            8,
        ]);
        $container->setDefinition('output.node.printer.pretty.example_step_setup', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettySetupPrinter', [
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference(ExceptionExtension::PRESENTER_ID),
            6,
        ]);
        $container->setDefinition('output.node.printer.pretty.example_setup', $definition);
    }

    /**
     * Loads statistics printer.
     */
    protected function loadStatisticsPrinter(ContainerBuilder $container)
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

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettyStatisticsPrinter', [
            new Reference('output.node.printer.counter'),
            new Reference('output.node.printer.list'),
        ]);
        $container->setDefinition('output.node.printer.pretty.statistics', $definition);
    }

    /**
     * Loads printer helpers.
     */
    protected function loadPrinterHelpers(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\Printer\Helper\WidthCalculator');
        $container->setDefinition('output.node.printer.pretty.width_calculator', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Helper\StepTextPainter', [
            new Reference(DefinitionExtension::PATTERN_TRANSFORMER_ID),
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
        ]);
        $container->setDefinition('output.node.printer.pretty.step_text_painter', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter');
        $container->setDefinition(self::RESULT_TO_STRING_CONVERTER_ID, $definition);
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
     * Creates root listener definition.
     *
     * @return Definition
     */
    protected function rearrangeBackgroundEvents($listener)
    {
        return new Definition('Behat\Behat\Output\Node\EventListener\Flow\FirstBackgroundFiresFirstListener', [
            new Definition('Behat\Behat\Output\Node\EventListener\Flow\OnlyFirstBackgroundFiresListener', [
                $listener,
            ]),
        ]);
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
        return new Definition(
            'Behat\Behat\Output\Node\EventListener\Flow\FireOnlySiblingsListener',
            [
                $beforeEventName,
                $afterEventName,
                new Definition('Behat\Testwork\Output\Node\EventListener\ChainEventListener', [$listeners]),
            ]
        );
    }

    /**
     * Creates contextual proxy listener.
     *
     * @param string $name
     *
     * @return Definition
     */
    protected function proxyEventsIfParameterIsSet($name, $value, Definition $listener)
    {
        return new Definition(
            'Behat\Testwork\Output\Node\EventListener\Flow\FireOnlyIfFormatterParameterListener',
            [$name, $value, $listener]
        );
    }

    /**
     * Processes all registered pretty formatter node listener wrappers.
     */
    protected function processListenerWrappers(ContainerBuilder $container)
    {
        $this->processor->processWrapperServices($container, self::ROOT_LISTENER_ID, self::ROOT_LISTENER_WRAPPER_TAG);
    }
}

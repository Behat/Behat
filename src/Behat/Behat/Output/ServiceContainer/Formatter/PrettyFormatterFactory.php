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
use Behat\Testwork\Exception\ServiceContainer\ExceptionExtension;
use Behat\Testwork\Output\ServiceContainer\Formatter\FormatterFactory;
use Behat\Testwork\Output\ServiceContainer\OutputExtension;
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
    const PRETTY_ROOT_LISTENER_ID = 'output.node.listener.pretty';

    /*
     * Available extension points
     */
    const PRETTY_ROOT_LISTENER_WRAPPER_TAG = 'output.node.listener.pretty.wrapper';

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
     * Loads pretty formatter node event listener.
     *
     * @param ContainerBuilder $container
     */
    protected function loadRootNodeListener(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Output\Node\EventListener\EventListeners', array(
            array(
                new Definition('Behat\Behat\Output\Node\EventListener\AST\ExerciseListener', array(
                    new Reference('output.node.printer.pretty.statistics')
                )),
                new Definition('Behat\Behat\Output\Node\EventListener\AST\FeatureListener', array(
                    new Reference('output.node.printer.pretty.feature')
                )),
                $this->proxySiblingEvents(
                    'Behat\Behat\EventDispatcher\Event\BackgroundTested',
                    array(
                        new Definition('Behat\Behat\Output\Node\EventListener\AST\ScenarioNodeListener', array(
                            'Behat\Behat\EventDispatcher\Event\BackgroundTested',
                            new Reference('output.node.printer.pretty.scenario')
                        )),
                        new Definition('Behat\Behat\Output\Node\EventListener\AST\StepListener', array(
                            new Reference('output.node.printer.pretty.step')
                        )),
                    )
                ),
                $this->proxySiblingEvents(
                    'Behat\Behat\EventDispatcher\Event\ScenarioTested',
                    array(
                        new Definition('Behat\Behat\Output\Node\EventListener\AST\ScenarioNodeListener', array(
                            'Behat\Behat\EventDispatcher\Event\ScenarioTested',
                            new Reference('output.node.printer.pretty.scenario')
                        )),
                        new Definition('Behat\Behat\Output\Node\EventListener\AST\StepListener', array(
                            new Reference('output.node.printer.pretty.step')
                        )),
                    )
                ),
                $this->proxySiblingEvents(
                    'Behat\Behat\EventDispatcher\Event\OutlineTested',
                    array(
                        $this->proxyEventsIfParameterIsSet(
                            'expand',
                            false,
                            new Definition('Behat\Behat\Output\Node\EventListener\AST\OutlineTableListener', array(
                                new Reference('output.node.printer.pretty.outline_table'),
                                new Reference('output.node.printer.pretty.example_row')
                            ))
                        ),
                        $this->proxyEventsIfParameterIsSet(
                            'expand',
                            true,
                            new Definition('Behat\Behat\Output\Node\EventListener\AST\OutlineListener', array(
                                new Reference('output.node.printer.pretty.outline'),
                                new Reference('output.node.printer.pretty.example'),
                                new Reference('output.node.printer.pretty.example_step')
                            ))
                        )
                    )
                ),
            )
        ));
        $container->setDefinition(self::PRETTY_ROOT_LISTENER_ID, $definition);
    }

    /**
     * Loads formatter itself.
     *
     * @param ContainerBuilder $container
     */
    protected function loadFormatter(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Output\NodeEventListeningFormatter', array(
            'pretty',
            'Prints the feature as is.',
            array(
                'expand'    => false,
                'paths'     => true,
                'multiline' => true,
            ),
            $this->createOutputPrinterDefinition(),
            $this->rearrangeBackgroundEvents(
                new Reference(self::PRETTY_ROOT_LISTENER_ID)
            ),
        ));
        $definition->addTag(OutputExtension::FORMATTER_TAG, array('priority' => 100));
        $container->setDefinition(OutputExtension::FORMATTER_TAG . '.pretty', $definition);
    }

    /**
     * Loads feature, scenario and step printers.
     *
     * @param ContainerBuilder $container
     */
    protected function loadCorePrinters(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettyFeaturePrinter');
        $container->setDefinition('output.node.printer.pretty.feature', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettyScenarioPrinter', array(
            new Reference('output.node.printer.pretty.scenario_width_calculator'),
            '%paths.base%'
        ));
        $container->setDefinition('output.node.printer.pretty.scenario', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettyStepPrinter', array(
            new Reference('output.node.printer.pretty.step_text_painter'),
            new Reference(ExceptionExtension::PRESENTER_ID),
            new Reference('output.node.printer.pretty.scenario_width_calculator')
        ));
        $container->setDefinition('output.node.printer.pretty.step', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettySkippedStepPrinter', array(
            new Reference('output.node.printer.pretty.step_text_painter'),
            new Reference('output.node.printer.pretty.scenario_width_calculator')
        ));
        $container->setDefinition('output.node.printer.pretty.skipped_step', $definition);
    }

    /**
     * Loads table outline printer.
     *
     * @param ContainerBuilder $container
     */
    protected function loadTableOutlinePrinter(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettyOutlineTablePrinter', array(
            new Reference('output.node.printer.pretty.scenario'),
            new Reference('output.node.printer.pretty.skipped_step')
        ));
        $container->setDefinition('output.node.printer.pretty.outline_table', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettyExampleRowPrinter', array(
            new Reference(ExceptionExtension::PRESENTER_ID)
        ));
        $container->setDefinition('output.node.printer.pretty.example_row', $definition);
    }

    /**
     * Loads expanded outline printer.
     *
     * @param ContainerBuilder $container
     */
    protected function loadExpandedOutlinePrinter(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettyOutlinePrinter', array(
            new Reference('output.node.printer.pretty.scenario'),
            new Reference('output.node.printer.pretty.skipped_step'),
        ));
        $container->setDefinition('output.node.printer.pretty.outline', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettyExamplePrinter', array(
            new Reference('output.node.printer.pretty.scenario_width_calculator'),
            '%paths.base%'
        ));
        $container->setDefinition('output.node.printer.pretty.example', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettyStepPrinter', array(
            new Reference('output.node.printer.pretty.step_text_painter'),
            new Reference(ExceptionExtension::PRESENTER_ID),
            new Reference('output.node.printer.pretty.scenario_width_calculator'),
            8
        ));
        $container->setDefinition('output.node.printer.pretty.example_step', $definition);
    }

    /**
     * Loads statistics printer.
     *
     * @param ContainerBuilder $container
     */
    protected function loadStatisticsPrinter(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\PrettyStatisticsPrinter', array(
            new Reference(TranslatorExtension::TRANSLATOR_ID),
            '%paths.base%'
        ));
        $container->setDefinition('output.node.printer.pretty.statistics', $definition);
    }

    /**
     * Loads printer helpers.
     *
     * @param ContainerBuilder $container
     */
    protected function loadPrinterHelpers(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\WidthCalculator');
        $container->setDefinition('output.node.printer.pretty.scenario_width_calculator', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Pretty\StepTextPainter', array(
            new Reference(DefinitionExtension::PATTERN_TRANSFORMER_ID)
        ));
        $container->setDefinition('output.node.printer.pretty.step_text_painter', $definition);
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
     * @param string       $eventClass
     * @param Definition[] $listeners
     *
     * @return Definition
     */
    protected function proxySiblingEvents($eventClass, array $listeners)
    {
        return new Definition('Behat\Behat\Output\Node\EventListener\Flow\FireOnlySiblingsListener',
            array(
                $eventClass,
                new Definition('Behat\Testwork\Output\Node\EventListener\EventListeners', array($listeners))
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
     * Processes all registered pretty formatter node listener wrappers.
     *
     * @param ContainerBuilder $container
     */
    protected function processListenerWrappers(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::PRETTY_ROOT_LISTENER_WRAPPER_TAG);

        foreach ($references as $reference) {
            $wrappedTester = $container->getDefinition(self::PRETTY_ROOT_LISTENER_ID);
            $wrappingTester = $container->getDefinition((string)$reference);
            $wrappingTester->replaceArgument(0, $wrappedTester);

            $container->setDefinition(self::PRETTY_ROOT_LISTENER_ID, $wrappingTester);
        }
    }
}

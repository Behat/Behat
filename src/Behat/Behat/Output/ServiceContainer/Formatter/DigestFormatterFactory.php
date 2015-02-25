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
 * Behat digest formatter factory.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DigestFormatterFactory implements FormatterFactory
{
    /**
     * @var ServiceProcessor
     */
    private $processor;

    /*
     * Available services
     */
    const ROOT_LISTENER_ID = 'output.node.listener.digest';
    const RESULT_TO_STRING_CONVERTER_ID = 'output.node.printer.result_to_string';

    /*
     * Available extension points
     */
    const ROOT_LISTENER_WRAPPER_TAG = 'output.node.listener.digest.wrapper';

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
     * Loads digest formatter node event listener.
     *
     * @param ContainerBuilder $container
     */
    protected function loadRootNodeListener(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Testwork\Output\Node\EventListener\ChainEventListener', array(
            array(
                new Definition('Behat\Behat\Output\Node\EventListener\AST\FeatureListener', array(
                    new Reference('output.node.printer.digest.feature'),
                    new Reference('output.node.printer.digest.feature_setup'),
                )),
                new Definition('Behat\Behat\Output\Node\EventListener\AST\ScenarioNodeListener', array(
                    ScenarioTested::AFTER_SETUP,
                    ScenarioTested::AFTER,
                    new Reference('output.node.printer.digest.scenario'),
                    new Reference('output.node.printer.digest.scenario_setup')
                )),
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
        $definition = new Definition('Behat\Testwork\Output\NodeEventListeningFormatter', array(
            'digest',
            'Prints the feature as is.',
            array(
            ),
            $this->createOutputPrinterDefinition(),
            new Reference(self::ROOT_LISTENER_ID),
        ));
        $definition->addTag(OutputExtension::FORMATTER_TAG, array('priority' => 100));
        $container->setDefinition(OutputExtension::FORMATTER_TAG . '.digest', $definition);
    }

    /**
     * Loads feature, scenario and step printers.
     *
     * @param ContainerBuilder $container
     */
    protected function loadCorePrinters(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\Printer\Digest\DigestFeaturePrinter', array(
            '%paths.base%'
        ));
        $container->setDefinition('output.node.printer.digest.feature', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Digest\DigestSetupPrinter', array(
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference(ExceptionExtension::PRESENTER_ID),
            0,
            true,
            true
        ));
        $container->setDefinition('output.node.printer.digest.feature_setup', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Digest\DigestScenarioPrinter');
        $container->setDefinition('output.node.printer.digest.scenario', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Digest\DigestSetupPrinter', array(
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference(ExceptionExtension::PRESENTER_ID),
            2
        ));
        $container->setDefinition('output.node.printer.digest.scenario_setup', $definition);
    }

    /**
     * Loads printer helpers.
     *
     * @param ContainerBuilder $container
     */
    protected function loadPrinterHelpers(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\Printer\Helper\WidthCalculator');
        $container->setDefinition('output.node.printer.pretty.width_calculator', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\Helper\StepTextPainter', array(
            new Reference(DefinitionExtension::PATTERN_TRANSFORMER_ID),
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID)
        ));
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
     * Processes all registered pretty formatter node listener wrappers.
     *
     * @param ContainerBuilder $container
     */
    protected function processListenerWrappers(ContainerBuilder $container)
    {
        $this->processor->processWrapperServices($container, self::ROOT_LISTENER_ID, self::ROOT_LISTENER_WRAPPER_TAG);
    }
}

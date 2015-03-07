<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\ServiceContainer\Formatter;

use Behat\Testwork\Output\ServiceContainer\OutputExtension;
use Behat\Testwork\Exception\ServiceContainer\ExceptionExtension;
use Behat\Testwork\Output\ServiceContainer\Formatter\FormatterFactory;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Behat junit formatter factory.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
final class JUnitFormatterFactory implements FormatterFactory
{
    /*
     * Available services
     */
    const ROOT_LISTENER_ID = 'output.node.listener.junit';
    const RESULT_TO_STRING_CONVERTER_ID = 'output.node.printer.result_to_string';

    /**
     * {@inheritdoc}
     */
    public function buildFormatter(ContainerBuilder $container)
    {
        $this->loadRootNodeListener($container);
        $this->loadPrinterHelpers($container);
        $this->loadCorePrinters($container);
        $this->loadFormatter($container);
    }

    /**
     * {@inheritdoc}
     */
    public function processFormatter(ContainerBuilder $container)
    {
    }

    /**
     * Loads printer helpers.
     *
     * @param ContainerBuilder $container
     */
    private function loadPrinterHelpers(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter');
        $container->setDefinition(self::RESULT_TO_STRING_CONVERTER_ID, $definition);
    }

    /**
     * Loads the printers used to print the basic JUnit report.
     *
     * @param ContainerBuilder $container
     */
    private function loadCorePrinters(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Node\Printer\JUnit\JUnitSuitePrinter', array(
            new Reference('output.junit.statistics'),
        ));
        $container->setDefinition('output.node.printer.junit.suite', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\JUnit\JUnitFeaturePrinter', array(
            new Reference('output.junit.statistics'),
        ));
        $container->setDefinition('output.node.printer.junit.feature', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\JUnit\JUnitScenarioPrinter', array(
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference('output.node.listener.junit.outline'),
        ));
        $container->setDefinition('output.node.printer.junit.scenario', $definition);

        $definition = new Definition('Behat\Behat\Output\Node\Printer\JUnit\JUnitStepPrinter', array(
            new Reference(ExceptionExtension::PRESENTER_ID),
        ));
        $container->setDefinition('output.node.printer.junit.step', $definition);
    }

    /**
     * Loads the node listeners required for JUnit printers to work.
     *
     * @param ContainerBuilder $container
     */
    private function loadRootNodeListener(ContainerBuilder $container)
    {

        $definition = new Definition('Behat\Behat\Output\Node\EventListener\JUnit\JUnitOutlineStoreListener', array(
                new Reference('output.node.printer.junit.suite')
            )
        );
        $container->setDefinition('output.node.listener.junit.outline', $definition);


        $definition = new Definition('Behat\Testwork\Output\Node\EventListener\ChainEventListener', array(
            array(
                new Reference('output.node.listener.junit.outline'),
                new Definition('Behat\Behat\Output\Node\EventListener\JUnit\JUnitFeatureElementListener', array(
                    new Reference('output.node.printer.junit.feature'),
                    new Reference('output.node.printer.junit.scenario'),
                    new Reference('output.node.printer.junit.step'),
                )),
            ),
        ));
        $container->setDefinition(self::ROOT_LISTENER_ID, $definition);
    }

    /**
     * Loads formatter itself.
     *
     * @param ContainerBuilder $container
     */
    private function loadFormatter(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\Statistics\PhaseStatistics');
        $container->setDefinition('output.junit.statistics', $definition);

        $definition = new Definition('Behat\Testwork\Output\NodeEventListeningFormatter', array(
            'junit',
            'Outputs the failures in JUnit compatible files.',
            array(
                'timer' => true,
            ),
            $this->createOutputPrinterDefinition(),
            new Definition('Behat\Testwork\Output\Node\EventListener\ChainEventListener', array(
                array(
                    new Reference(self::ROOT_LISTENER_ID),
                    new Definition('Behat\Behat\Output\Node\EventListener\Statistics\ScenarioStatsListener', array(
                        new Reference('output.junit.statistics')
                    )),
                    new Definition('Behat\Behat\Output\Node\EventListener\Statistics\StepStatsListener', array(
                        new Reference('output.junit.statistics'),
                        new Reference(ExceptionExtension::PRESENTER_ID)
                    )),
                    new Definition('Behat\Behat\Output\Node\EventListener\Statistics\HookStatsListener', array(
                        new Reference('output.junit.statistics'),
                        new Reference(ExceptionExtension::PRESENTER_ID)
                    )),
                ),
            )),
        ));
        $definition->addTag(OutputExtension::FORMATTER_TAG, array('priority' => 100));
        $container->setDefinition(OutputExtension::FORMATTER_TAG . '.junit', $definition);
    }

    /**
     * Creates output printer definition.
     *
     * @return Definition
     */
    private function createOutputPrinterDefinition()
    {
        return new Definition('Behat\Testwork\Output\Printer\JUnitOutputPrinter', array(
            new Definition('Behat\Testwork\Output\Printer\Factory\FilesystemOutputFactory'),
        ));
    }
}

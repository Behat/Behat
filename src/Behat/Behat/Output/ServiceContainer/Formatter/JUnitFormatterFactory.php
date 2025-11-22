<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\ServiceContainer\Formatter;

use Behat\Behat\Output\Node\EventListener\JUnit\JUnitDurationListener;
use Behat\Behat\Output\Node\EventListener\JUnit\JUnitFeatureElementListener;
use Behat\Behat\Output\Node\EventListener\JUnit\JUnitOutlineStoreListener;
use Behat\Behat\Output\Node\EventListener\Statistics\HookStatsListener;
use Behat\Behat\Output\Node\EventListener\Statistics\ScenarioStatsListener;
use Behat\Behat\Output\Node\EventListener\Statistics\StepStatsListener;
use Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter;
use Behat\Behat\Output\Node\Printer\JUnit\JUnitFeaturePrinter;
use Behat\Behat\Output\Node\Printer\JUnit\JUnitScenarioPrinter;
use Behat\Behat\Output\Node\Printer\JUnit\JUnitSetupPrinter;
use Behat\Behat\Output\Node\Printer\JUnit\JUnitStepPrinter;
use Behat\Behat\Output\Node\Printer\JUnit\JUnitSuitePrinter;
use Behat\Behat\Output\Statistics\PhaseStatistics;
use Behat\Config\Formatter\JUnitFormatter;
use Behat\Testwork\Exception\ServiceContainer\ExceptionExtension;
use Behat\Testwork\Output\Node\EventListener\ChainEventListener;
use Behat\Testwork\Output\NodeEventListeningFormatter;
use Behat\Testwork\Output\Printer\Factory\FilesystemOutputFactory;
use Behat\Testwork\Output\Printer\JUnitOutputPrinter;
use Behat\Testwork\Output\ServiceContainer\Formatter\FormatterFactory;
use Behat\Testwork\Output\ServiceContainer\OutputExtension;
use Behat\Testwork\PathOptions\ServiceContainer\PathOptionsExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

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
    public const ROOT_LISTENER_ID = 'output.node.listener.junit';
    public const RESULT_TO_STRING_CONVERTER_ID = 'output.node.printer.result_to_string';

    public function buildFormatter(ContainerBuilder $container)
    {
        $this->loadRootNodeListener($container);
        $this->loadPrinterHelpers($container);
        $this->loadCorePrinters($container);
        $this->loadFormatter($container);
    }

    public function processFormatter(ContainerBuilder $container)
    {
    }

    /**
     * Loads printer helpers.
     */
    private function loadPrinterHelpers(ContainerBuilder $container)
    {
        $definition = new Definition(ResultToStringConverter::class);
        $container->setDefinition(self::RESULT_TO_STRING_CONVERTER_ID, $definition);
    }

    /**
     * Loads the printers used to print the basic JUnit report.
     */
    private function loadCorePrinters(ContainerBuilder $container)
    {
        $definition = new Definition(JUnitSuitePrinter::class, [
            new Reference('output.junit.statistics'),
        ]);
        $container->setDefinition('output.node.printer.junit.suite', $definition);

        $definition = new Definition(JUnitFeaturePrinter::class, [
            new Reference('output.junit.statistics'),
            new Reference('output.node.listener.junit.duration'),
            new Reference(PathOptionsExtension::CONFIGURABLE_PATH_PRINTER_ID),
        ]);
        $container->setDefinition('output.node.printer.junit.feature', $definition);

        $definition = new Definition(JUnitScenarioPrinter::class, [
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference('output.node.listener.junit.duration'),
            new Reference(PathOptionsExtension::CONFIGURABLE_PATH_PRINTER_ID),
        ]);
        $container->setDefinition('output.node.printer.junit.scenario', $definition);

        $definition = new Definition(JUnitStepPrinter::class, [
            new Reference(ExceptionExtension::PRESENTER_ID),
        ]);
        $container->setDefinition('output.node.printer.junit.step', $definition);

        $definition = new Definition(
            JUnitSetupPrinter::class,
            [
                new Reference(ExceptionExtension::PRESENTER_ID),
            ]
        );
        $container->setDefinition('output.node.printer.junit.setup', $definition);
    }

    /**
     * Loads the node listeners required for JUnit printers to work.
     */
    private function loadRootNodeListener(ContainerBuilder $container)
    {
        $definition = new Definition(
            JUnitOutlineStoreListener::class,
            [
                new Reference('output.node.printer.junit.suite'),
            ]
        );
        $container->setDefinition('output.node.listener.junit.outline', $definition);

        $definition = new Definition(
            JUnitDurationListener::class
        );

        $container->setDefinition('output.node.listener.junit.duration', $definition);

        $definition = new Definition(ChainEventListener::class, [
            [
                new Reference('output.node.listener.junit.duration'),
                new Definition(JUnitFeatureElementListener::class, [
                    new Reference('output.node.printer.junit.feature'),
                    new Reference('output.node.printer.junit.scenario'),
                    new Reference('output.node.printer.junit.step'),
                    new Reference('output.node.printer.junit.setup'),
                ]),
                new Reference('output.node.listener.junit.outline'),
            ],
        ]);
        $container->setDefinition(self::ROOT_LISTENER_ID, $definition);
    }

    /**
     * Loads formatter itself.
     */
    private function loadFormatter(ContainerBuilder $container)
    {
        $definition = new Definition(PhaseStatistics::class);
        $container->setDefinition('output.junit.statistics', $definition);

        $definition = new Definition(NodeEventListeningFormatter::class, [
            JUnitFormatter::NAME,
            'Outputs the failures in JUnit compatible files.',
            JUnitFormatter::defaults(),
            $this->createOutputPrinterDefinition(),
            new Definition(ChainEventListener::class, [
                [
                    new Reference(self::ROOT_LISTENER_ID),
                    new Definition(ScenarioStatsListener::class, [
                        new Reference('output.junit.statistics'),
                    ]),
                    new Definition(StepStatsListener::class, [
                        new Reference('output.junit.statistics'),
                        new Reference(ExceptionExtension::PRESENTER_ID),
                    ]),
                    new Definition(HookStatsListener::class, [
                        new Reference('output.junit.statistics'),
                        new Reference(ExceptionExtension::PRESENTER_ID),
                    ]),
                ],
            ]),
        ]);
        $definition->addTag(OutputExtension::FORMATTER_TAG, ['priority' => 100]);
        $container->setDefinition(OutputExtension::FORMATTER_TAG . '.junit', $definition);
    }

    /**
     * Creates output printer definition.
     *
     * @return Definition
     */
    private function createOutputPrinterDefinition()
    {
        return new Definition(JUnitOutputPrinter::class, [
            new Definition(FilesystemOutputFactory::class),
        ]);
    }
}

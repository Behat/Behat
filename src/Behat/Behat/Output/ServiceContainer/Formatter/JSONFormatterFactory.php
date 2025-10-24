<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\ServiceContainer\Formatter;

use Behat\Behat\Output\Node\EventListener\JSON\JSONDurationListener;
use Behat\Behat\Output\Node\EventListener\JSON\JSONElementListener;
use Behat\Behat\Output\Node\EventListener\Statistics\HookStatsListener;
use Behat\Behat\Output\Node\EventListener\Statistics\ScenarioStatsListener;
use Behat\Behat\Output\Node\EventListener\Statistics\StepStatsListener;
use Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter;
use Behat\Behat\Output\Node\Printer\JSON\JSONExercisePrinter;
use Behat\Behat\Output\Node\Printer\JSON\JSONFeaturePrinter;
use Behat\Behat\Output\Node\Printer\JSON\JSONScenarioPrinter;
use Behat\Behat\Output\Node\Printer\JSON\JSONSetupPrinter;
use Behat\Behat\Output\Node\Printer\JSON\JSONStepPrinter;
use Behat\Behat\Output\Node\Printer\JSON\JSONSuitePrinter;
use Behat\Behat\Output\Statistics\PhaseStatistics;
use Behat\Config\Formatter\JSONFormatter;
use Behat\Testwork\Exception\ServiceContainer\ExceptionExtension;
use Behat\Testwork\Output\Node\EventListener\ChainEventListener;
use Behat\Testwork\Output\NodeEventListeningFormatter;
use Behat\Testwork\Output\Printer\Factory\FileOutputFactory;
use Behat\Testwork\Output\Printer\JSONOutputPrinter;
use Behat\Testwork\Output\ServiceContainer\Formatter\FormatterFactory;
use Behat\Testwork\Output\ServiceContainer\OutputExtension;
use Behat\Testwork\PathOptions\ServiceContainer\PathOptionsExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class JSONFormatterFactory implements FormatterFactory
{
    public const ROOT_LISTENER_ID = 'output.node.listener.json';
    public const RESULT_TO_STRING_CONVERTER_ID = 'output.node.printer.result_to_string';

    public function buildFormatter(ContainerBuilder $container): void
    {
        $this->loadRootNodeListener($container);
        $this->loadPrinterHelpers($container);
        $this->loadCorePrinters($container);
        $this->loadFormatter($container);
    }

    public function processFormatter(ContainerBuilder $container): void
    {
    }

    private function loadPrinterHelpers(ContainerBuilder $container): void
    {
        $definition = new Definition(ResultToStringConverter::class);
        $container->setDefinition(self::RESULT_TO_STRING_CONVERTER_ID, $definition);
    }

    private function loadCorePrinters(ContainerBuilder $container): void
    {
        $definition = new Definition(JSONExercisePrinter::class, [
            new Reference('output.json.statistics.exercise'),
            new Reference('output.node.listener.json.duration'),
        ]);
        $container->setDefinition('output.node.printer.json.exercise', $definition);

        $definition = new Definition(JSONSuitePrinter::class, [
            new Reference('output.json.statistics.suite'),
            new Reference('output.node.listener.json.duration'),
        ]);
        $container->setDefinition('output.node.printer.json.suite', $definition);

        $definition = new Definition(JSONFeaturePrinter::class, [
            new Reference('output.json.statistics.feature'),
            new Reference('output.node.listener.json.duration'),
        ]);
        $container->setDefinition('output.node.printer.json.feature', $definition);

        $definition = new Definition(JSONScenarioPrinter::class, [
            new Reference(self::RESULT_TO_STRING_CONVERTER_ID),
            new Reference('output.node.listener.json.duration'),
            new Reference(PathOptionsExtension::CONFIGURABLE_PATH_PRINTER_ID),
        ]);
        $container->setDefinition('output.node.printer.json.scenario', $definition);

        $definition = new Definition(JSONStepPrinter::class, [
            new Reference(ExceptionExtension::PRESENTER_ID),
        ]);
        $container->setDefinition('output.node.printer.json.step', $definition);

        $definition = new Definition(
            JSONSetupPrinter::class,
            [
                new Reference(ExceptionExtension::PRESENTER_ID),
            ]
        );
        $container->setDefinition('output.node.printer.json.setup', $definition);
    }

    private function loadRootNodeListener(ContainerBuilder $container): void
    {
        $definition = new Definition(
            JSONDurationListener::class
        );

        $container->setDefinition('output.node.listener.json.duration', $definition);

        $definition = new Definition(ChainEventListener::class, [
            [
                new Reference('output.node.listener.json.duration'),
                new Definition(JSONElementListener::class, [
                    new Reference('output.node.printer.json.exercise'),
                    new Reference('output.node.printer.json.suite'),
                    new Reference('output.node.printer.json.feature'),
                    new Reference('output.node.printer.json.scenario'),
                    new Reference('output.node.printer.json.step'),
                    new Reference('output.node.printer.json.setup'),
                ]),
            ],
        ]);
        $container->setDefinition(self::ROOT_LISTENER_ID, $definition);
    }

    private function loadFormatter(ContainerBuilder $container): void
    {
        $definition = new Definition(PhaseStatistics::class);
        $container->setDefinition('output.json.statistics.exercise', $definition);

        $definition = new Definition(PhaseStatistics::class);
        $container->setDefinition('output.json.statistics.suite', $definition);

        $definition = new Definition(PhaseStatistics::class);
        $container->setDefinition('output.json.statistics.feature', $definition);

        $definition = new Definition(NodeEventListeningFormatter::class, [
            JSONFormatter::NAME,
            'Outputs the failures in JSON files.',
            JSONFormatter::defaults(),
            $this->createOutputPrinterDefinition(),
            new Definition(ChainEventListener::class, [
                [
                    new Reference(self::ROOT_LISTENER_ID),
                    new Definition(ScenarioStatsListener::class, [
                        new Reference('output.json.statistics.exercise'),
                    ]),
                    new Definition(StepStatsListener::class, [
                        new Reference('output.json.statistics.exercise'),
                        new Reference(ExceptionExtension::PRESENTER_ID),
                    ]),
                    new Definition(HookStatsListener::class, [
                        new Reference('output.json.statistics.exercise'),
                        new Reference(ExceptionExtension::PRESENTER_ID),
                    ]),
                    new Definition(ScenarioStatsListener::class, [
                        new Reference('output.json.statistics.suite'),
                    ]),
                    new Definition(StepStatsListener::class, [
                        new Reference('output.json.statistics.suite'),
                        new Reference(ExceptionExtension::PRESENTER_ID),
                    ]),
                    new Definition(HookStatsListener::class, [
                        new Reference('output.json.statistics.suite'),
                        new Reference(ExceptionExtension::PRESENTER_ID),
                    ]),
                    new Definition(ScenarioStatsListener::class, [
                        new Reference('output.json.statistics.feature'),
                    ]),
                    new Definition(StepStatsListener::class, [
                        new Reference('output.json.statistics.feature'),
                        new Reference(ExceptionExtension::PRESENTER_ID),
                    ]),
                    new Definition(HookStatsListener::class, [
                        new Reference('output.json.statistics.feature'),
                        new Reference(ExceptionExtension::PRESENTER_ID),
                    ]),
                ],
            ]),
        ]);
        $definition->addTag(OutputExtension::FORMATTER_TAG, ['priority' => 100]);
        $container->setDefinition(OutputExtension::FORMATTER_TAG . '.json', $definition);
    }

    private function createOutputPrinterDefinition(): Definition
    {
        return new Definition(JSONOutputPrinter::class, [
            new Definition(FileOutputFactory::class),
        ]);
    }
}

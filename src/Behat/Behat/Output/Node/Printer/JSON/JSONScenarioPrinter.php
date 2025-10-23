<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output\Node\Printer\JSON;

use Behat\Behat\Output\Node\EventListener\JSON\JSONDurationListener;
use Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter;
use Behat\Behat\Output\Node\Printer\ScenarioPrinter;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\NamedScenarioInterface;
use Behat\Gherkin\Node\ScenarioLikeInterface;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\JSONOutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;

final class JSONScenarioPrinter implements ScenarioPrinter
{
    private ScenarioLikeInterface $currentScenario;

    private FeatureNode $currentFeature;

    public function __construct(
        private readonly ResultToStringConverter $resultConverter,
        private readonly JSONDurationListener $durationListener,
    ) {
    }

    public function printHeader(
        Formatter $formatter,
        FeatureNode $feature,
        ScenarioLikeInterface $scenario,
    ): void {
        $this->currentScenario = $scenario;
        $this->currentFeature = $feature;
        $outputPrinter = $formatter->getOutputPrinter();
        assert($outputPrinter instanceof JSONOutputPrinter);
        $outputPrinter->addScenario();
    }

    public function printFooter(Formatter $formatter, TestResult $result): void
    {
        $scenario = $this->currentScenario;
        assert($scenario instanceof NamedScenarioInterface);
        $name = implode(' ', array_map(fn ($l) => trim($l), explode("\n", $scenario->getName() ?? '')));

        $scenarioAttributes = [
            'name' => $name,
            'time' => (float) $this->durationListener->getDuration($scenario),
            'status' => $this->resultConverter->convertResultToString($result),
        ];

        $file = $this->currentFeature->getFile();
        if ($file) {
            $cwd = realpath(getcwd());
            $scenarioAttributes['file'] =
                str_starts_with($file, $cwd) ?
                    ltrim(substr($file, strlen($cwd)), DIRECTORY_SEPARATOR) : $file;
        }

        $outputPrinter = $formatter->getOutputPrinter();
        assert($outputPrinter instanceof JSONOutputPrinter);

        $outputPrinter->addCurrentScenarioAttributes($scenarioAttributes, true);
    }
}

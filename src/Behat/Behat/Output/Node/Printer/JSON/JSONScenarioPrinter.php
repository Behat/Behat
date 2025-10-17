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
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\ScenarioLikeInterface;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\JSONOutputPrinter;
use Behat\Testwork\Tester\Result\TestResult;

final class JSONScenarioPrinter implements ScenarioPrinter
{
    /**
     * @var array<int, OutlineNode>
     */
    private array $outlineMap = [];

    private ?OutlineNode $lastOutline = null;

    private int $outlineStepCount;

    private ?ScenarioLikeInterface $currentScenario = null;

    private ?FeatureNode $currentFeature = null;

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
        $name = implode(' ', array_map(fn ($l) => trim($l), explode("\n", $scenario->getTitle() ?? '')));

        if ($scenario instanceof ExampleNode) {
            $name = $this->buildExampleName($scenario);
        }

        $scenarioAttributes = [
            'name' => $name,
            'time' => $this->durationListener->getDuration($scenario),
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

    public function saveOutline(int $line, OutlineNode $outline): void
    {
        $this->outlineMap[$line] = $outline;
    }

    private function buildExampleName(ExampleNode $scenario): string
    {
        $currentOutline = $this->outlineMap[$scenario->getLine()];
        if ($currentOutline === $this->lastOutline) {
            ++$this->outlineStepCount;
        } else {
            $this->lastOutline = $currentOutline;
            $this->outlineStepCount = 1;
        }

        $name = $currentOutline->getTitle() . ' #' . $this->outlineStepCount;

        return $name;
    }
}

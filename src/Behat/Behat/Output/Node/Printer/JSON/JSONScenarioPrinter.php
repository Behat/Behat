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
use Behat\Gherkin\Node\DescribableNodeInterface;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\NamedScenarioInterface;
use Behat\Gherkin\Node\ScenarioLikeInterface;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\JSONOutputPrinter;
use Behat\Testwork\PathOptions\Printer\ConfigurablePathPrinter;
use Behat\Testwork\Tester\Result\TestResult;

final class JSONScenarioPrinter implements ScenarioPrinter
{
    private ScenarioLikeInterface $currentScenario;

    private FeatureNode $currentFeature;

    public function __construct(
        private readonly ResultToStringConverter $resultConverter,
        private readonly JSONDurationListener $durationListener,
        private readonly ConfigurablePathPrinter $configurablePathPrinter,
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
        ['name' => $name, 'description' => $description] = $this->getNameAndDescription($scenario);

        $scenarioAttributes = [
            'name' => $name,
        ];
        if ($description !== null) {
            $scenarioAttributes['description'] = $description;
        }

        if ($formatter->getParameter('timer')) {
            $scenarioAttributes['time'] = (float) $this->durationListener->getDuration($scenario);
        }

        $scenarioAttributes['status'] = $this->resultConverter->convertResultToString($result);

        $file = $this->currentFeature->getFile();
        if ($file) {
            $scenarioAttributes['file'] = $this->configurablePathPrinter->processPathsInText(
                $file,
                applyEditorUrl: false,
            );
        }

        $outputPrinter = $formatter->getOutputPrinter();
        assert($outputPrinter instanceof JSONOutputPrinter);

        $outputPrinter->addCurrentScenarioAttributes($scenarioAttributes, true);
    }

    /**
     * @return array{name: ?string, description: ?string}
     */
    private function getNameAndDescription(NamedScenarioInterface $scenario): array
    {
        $scenarioName = $scenario->getName() ?? '';

        if ($scenario instanceof DescribableNodeInterface && $scenario->getDescription()) {
            // All ScenarioLikeInterface defined by behat/gherkin are also DescribableNodeInterface
            // but we can't guarantee that's true if the node has come from third-party code.
            //
            // If it does match this interface and was parsed in gherkin-32 mode the description
            // will be in the description property and the title is guaranteed to be a single line.
            //
            // Remove any internal padding from the description as this may not always be consistent and is of no real
            // use to the user.
            return [
                'name' => $scenarioName,
                'description' => implode("\n", array_map(trim(...), explode("\n", $scenario->getDescription()))),
            ];
        }

        if (!str_contains($scenarioName, "\n")) {
            // It was parsed in gherkin-32 mode with no description, or in legacy mode with a single-line name/title
            // Either way the output is just the name.
            return [
                'name' => $scenarioName,
                'description' => null,
            ];
        }

        // It was parsed in legacy mode and has a multi-line name/title. For consistency with previous Behat versions,
        // we trim and join all the lines together to produce a one-line "name".
        return [
            'name' => implode(' ', array_map(trim(...), explode("\n", $scenarioName))),
            'description' => null,
        ];
    }
}

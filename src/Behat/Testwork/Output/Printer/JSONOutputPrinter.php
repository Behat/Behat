<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Printer;

use Behat\Testwork\Output\Exception\MissingOutputPathException;
use Behat\Testwork\Output\Printer\Factory\FileOutputFactory;
use Symfony\Component\Console\Output\OutputInterface;

final class JSONOutputPrinter extends StreamOutputPrinter
{
    /**
     * @var array<string, string|int|float|array>|null
     */
    private ?array $exercise = null;

    /**
     * @var array<string, string|int|float|array>[]
     */
    private array $suites = [];

    /**
     * @var array<string, string|int|float|array>[]
     */
    private array $features = [];

    /**
     * @var array<string, string|int|float|array>[]
     */
    private array $scenarios = [];

    /**
     * @var array<string, string|int|float|array>
     */
    private array $currentSuite = [];

    /**
     * @var array<string, string|int|float|array>
     */
    private array $currentFeature = [];

    /**
     * @var array<string, string|int|float|array>
     */
    private array $currentScenario = [];

    private int $scenariosCount = 0;

    private int $featuresCount = 0;

    private int $suitesCount = 0;

    public function __construct(FileOutputFactory $outputFactory)
    {
        parent::__construct($outputFactory);
    }

    public function createNewFile(): void
    {
        $this->exercise = [
            'suites' => &$this->suites,
        ];

        $this->flush();
    }

    /**
     * @param array<string, string|int|float> $exerciseAttributes
     */
    public function extendExerciseAttributes(array $exerciseAttributes): void
    {
        $this->addAttributesToNode($this->exercise, $exerciseAttributes, true);
    }

    /**
     * @param array<string, string|int|float> $suiteAttributes
     */
    public function addSuite(array $suiteAttributes = []): void
    {
        $this->suites[] = [];
        $this->currentSuite = &$this->suites[$this->suitesCount++];
        $this->currentSuite['features'] = [];
        $this->features = &$this->currentSuite['features'];
        $this->featuresCount = 0;
        $this->addAttributesToNode($this->currentSuite, $suiteAttributes);
    }

    /**
     * @param array<string, string|int|float> $suiteAttributes
     */
    public function extendSuiteAttributes(array $suiteAttributes): void
    {
        $this->addAttributesToNode($this->currentSuite, $suiteAttributes, true);
    }

    /**
     * @param array<string, string|int|float> $featureAttributes
     */
    public function addFeature(array $featureAttributes = []): void
    {
        $this->features[] = [];
        $this->currentFeature = &$this->features[$this->featuresCount++];
        $this->currentFeature['scenarios'] = [];
        $this->scenarios = &$this->currentFeature['scenarios'];
        $this->scenariosCount = 0;
        $this->addAttributesToNode($this->currentFeature, $featureAttributes);
    }

    /**
     * @param array<string, string|int|float> $featureAttributes
     */
    public function extendFeatureAttributes(array $featureAttributes): void
    {
        $this->addAttributesToNode($this->currentFeature, $featureAttributes, true);
    }

    /**
     * @param array<string, string|int|float> $scenarioAttributes
     */
    public function addScenario(array $scenarioAttributes = []): void
    {
        $this->scenarios[] = [];
        $this->currentScenario = &$this->scenarios[$this->scenariosCount++];
        $this->addAttributesToNode($this->currentScenario, $scenarioAttributes);
    }

    /**
     * @param array<string, string|int|float> $scenarioAttributes
     */
    public function addCurrentScenarioAttributes(array $scenarioAttributes, bool $atStart = false): void
    {
        $this->addAttributesToNode($this->currentScenario, $scenarioAttributes, $atStart);
    }

    /**
     * @param array<string, string|int|float> $nodeAttributes
     */
    public function addSuiteChild(string $nodeName, array $nodeAttributes = []): void
    {
        $childNode = [];
        if (!isset($this->currentSuite[$nodeName])) {
            $this->currentSuite[$nodeName] = [];
        }
        $this->currentSuite[$nodeName][] = &$childNode;
        $this->addAttributesToNode($childNode, $nodeAttributes);
    }

    /**
     * @param array<string, string|int|float> $nodeAttributes
     */
    public function addFeatureChild(string $nodeName, array $nodeAttributes = []): void
    {
        $childNode = [];
        if (!isset($this->currentFeature[$nodeName])) {
            $this->currentFeature[$nodeName] = [];
        }
        $this->currentFeature[$nodeName][] = &$childNode;
        $this->addAttributesToNode($childNode, $nodeAttributes);
    }

    /**
     * @param array<string, string|int|float> $nodeAttributes
     */
    public function addScenarioChild(string $nodeName, array $nodeAttributes = []): void
    {
        $childNode = [];
        if (!isset($this->currentScenario[$nodeName])) {
            $this->currentScenario[$nodeName] = [];
        }
        $this->currentScenario[$nodeName][] = &$childNode;
        $this->addAttributesToNode($childNode, $nodeAttributes);
    }

    public function flush(): void
    {
        if (is_array($this->exercise)) {
            try {
                $this->getWritingStream()->write(
                    json_encode($this->exercise, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
                    false,
                    OutputInterface::OUTPUT_RAW
                );
            } catch (MissingOutputPathException) {
                throw new MissingOutputPathException(
                    'The `output_path` option must be specified for the json formatter.',
                );
            }
        }

        parent::flush();
    }

    /**
     * @param array<string, string|int|float|array> $node
     * @param array<string, string|int|float> $attributes
     */
    private function addAttributesToNode(array &$node, array $attributes, bool $atStart = false): void
    {
        $node = $atStart ? [...$attributes, ...$node] : [...$node, ...$attributes];
    }
}

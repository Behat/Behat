<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output;

use Behat\Behat\Definition\Definition;
use Behat\Behat\Definition\Pattern\PatternTransformer;
use Behat\Behat\Hook\Call\AfterFeature;
use Behat\Behat\Hook\Call\AfterScenario;
use Behat\Behat\Output\Printer\ConsoleOutputPrinter;
use Behat\Behat\Tester\Event\BackgroundTested;
use Behat\Behat\Tester\Event\ExampleTested;
use Behat\Behat\Tester\Event\FeatureTested;
use Behat\Behat\Tester\Event\OutlineTested;
use Behat\Behat\Tester\Event\ScenarioTested;
use Behat\Behat\Tester\Event\StepTested;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Tester\Result\StepTestResult;
use Behat\Behat\Tester\Result\TestResult;
use Behat\Gherkin\Node\BackgroundNode;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\ExampleTableNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Gherkin\Node\ScenarioLikeInterface;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Call\CallResult;
use Behat\Testwork\Call\CallResults;
use Behat\Testwork\Counter\MemoryUsage;
use Behat\Testwork\Counter\Timer;
use Behat\Testwork\Exception\ExceptionPresenter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Output\TranslatableCliFormatter;
use Behat\Testwork\Tester\Event\ExerciseCompleted;
use Behat\Testwork\Tester\Event\SuiteTested;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Behat pretty formatter.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PrettyFormatter extends TranslatableCliFormatter
{
    /**
     * @var PatternTransformer
     */
    private $patternTransformer;
    /**
     * @var string
     */
    private $basePath;
    /**
     * @var Boolean
     */
    private $inOutline = false;
    /**
     * @var Boolean
     */
    private $backgroundPrinted = false;
    /**
     * @var Boolean
     */
    private $inBackground = false;
    /**
     * @var OutlineNode|ScenarioNode
     */
    private $currentScenario;
    /**
     * @var ExampleTested
     */
    private $currentExampleEvent;
    /**
     * @var integer
     */
    private $currentMaxLineLength = 0;
    /**
     * @var StepTested[]
     */
    private $currentBeforeStepEvents = array();
    /**
     * @var StepTested[]
     */
    private $currentAfterStepEvents = array();
    /**
     * @var string[]
     */
    private $failedScenarioPaths = array();
    /**
     * @var array
     */
    private $scenarioStats;
    /**
     * @var array
     */
    private $stepStats;
    /**
     * @var Timer
     */
    private $timer;

    public function __construct(
        OutputPrinter $printer,
        ExceptionPresenter $exceptionPresenter,
        TranslatorInterface $translator,
        PatternTransformer $patternTransformer,
        $basePath
    ) {
        parent::__construct($printer, $exceptionPresenter, $translator);

        $this->patternTransformer = $patternTransformer;
        $this->scenarioStats = $this->stepStats = array(
            TestResult::PASSED    => 0,
            TestResult::FAILED    => 0,
            TestResult::UNDEFINED => 0,
            TestResult::PENDING   => 0,
            TestResult::SKIPPED   => 0
        );

        if (null !== $basePath) {
            $realBasePath = realpath($basePath);

            if ($realBasePath) {
                $basePath = $realBasePath;
            }
        }

        $this->basePath = $basePath;

        $this->setParameter('multiline', true);
        $this->setParameter('expand', false);
        $this->setParameter('paths', true);
        $this->setParameter('timer', true);
    }

    public static function getSubscribedEvents()
    {
        return array(
            ExerciseCompleted::BEFORE => array('startExerciseTimer', 999),
            ExerciseCompleted::AFTER  => array('printCounters', -50),
            SuiteTested::BEFORE       => array('printSuiteHeader', -50),
            SuiteTested::AFTER        => array('printSuiteFooter', -50),
            FeatureTested::BEFORE     => array('printFeatureHeader', -50),
            FeatureTested::AFTER      => array('printFeatureFooter', -50),
            ScenarioTested::BEFORE    => array('printScenarioHeader', -50),
            ScenarioTested::AFTER     => array('printScenarioFooter', -50),
            OutlineTested::BEFORE     => array('printOutlineHeader', -50),
            OutlineTested::AFTER      => array('printOutlineFooter', -50),
            ExampleTested::BEFORE     => array('printExampleRowHeader'),
            ExampleTested::AFTER      => array(array('printExamplesHeader', -30), array('printExampleRow', -50)),
            BackgroundTested::BEFORE  => array('printBackgroundHeader', -50),
            BackgroundTested::AFTER   => array('printBackgroundFooter', -50),
            StepTested::BEFORE        => array('printStepHeader', -50),
            StepTested::AFTER         => array('printStepFooter', -50),
        );
    }

    public function getName()
    {
        return 'pretty';
    }

    public function getDescription()
    {
        return 'Prints the feature as is.';
    }

    public function startExerciseTimer()
    {
        $this->timer = new Timer();
        $this->timer->start();
    }

    public function printSuiteHeader(SuiteTested $event)
    {
        $this->printBeforeHookCallResults($event->getHookCallResults(), '');
    }

    public function printSuiteFooter(SuiteTested $event)
    {
        $this->printAfterHookCallResults($event->getHookCallResults(), '');
    }

    public function printFeatureHeader(FeatureTested $event)
    {
        $this->backgroundPrinted = false;

        $this->printBeforeHookCallResults($event->getHookCallResults(), '');

        $feature = $event->getFeature();
        if ($feature->hasTags()) {
            $tags = array_map(function ($tag) { return '@' . $tag; }, $feature->getTags());
            $this->writeln(sprintf('{+tag}%s{-tag}', implode(' ', $tags)));
        }

        $this->write(sprintf('{+keyword}%s:{-keyword}', $feature->getKeyword()));
        if ($feature->getTitle()) {
            $this->write(sprintf(' %s', $feature->getTitle()));
        }
        $this->writeln();

        if (!$feature->hasDescription()) {
            $this->writeln();

            return;
        }

        foreach (explode("\n", $feature->getDescription()) as $line) {
            $this->writeln(sprintf('  %s', $line));
        }
        $this->writeln();
    }

    public function printFeatureFooter(FeatureTested $event)
    {
        $this->printAfterHookCallResults($event->getHookCallResults(), '');
    }

    public function printScenarioHeader(ScenarioTested $event)
    {
        $this->printBeforeHookCallResults($event->getHookCallResults(), '  ');
        $this->currentScenario = $event->getScenario();

        $this->calculateCurrentMaxLineLength($event->getScenario(), $event->getFeature()->getBackground());
        if ($event->getFeature()->hasBackground() && !$this->backgroundPrinted) {
            return;
        }

        $this->printFullScenarioHeader($event->getScenario(), $event->getFeature());
    }

    public function printFullScenarioHeader(ScenarioInterface $scenario, FeatureNode $feature)
    {
        if ($scenario->hasTags()) {
            $tags = array_map(function ($tag) { return '@' . $tag; }, $scenario->getTags());
            $this->writeln(sprintf('  {+tag}%s{-tag}', implode(' ', $tags)));
        }

        $this->printScenarioTitle($scenario, $feature);
    }

    public function printScenarioTitle(ScenarioLikeInterface $scenario, FeatureNode $feature)
    {
        $description = explode("\n", $scenario->getTitle());
        $title = array_shift($description);

        $this->write(sprintf('  {+keyword}%s:{-keyword}', $scenario->getKeyword()));

        $combinedStringLength = mb_strlen($scenario->getKeyword(), 'utf8') + 3;
        if ($title) {
            $combinedStringLength += mb_strlen($title, 'utf8') + 1;
            $this->write(sprintf(' %s', $title));
        }

        if ($this->getParameter('paths')) {
            $this->write(
                sprintf(
                    '%s {+comment}# %s:%d{-comment}',
                    str_pad('', $this->currentMaxLineLength - $combinedStringLength),
                    $this->relativizePath($feature->getFile()),
                    $scenario->getLine()
                )
            );
        }
        $this->writeln();

        foreach ($description as $line) {
            $this->writeln(sprintf('    %s', $line));
        }
    }

    public function printScenarioFooter(ScenarioTested $event)
    {
        $this->printAfterHookCallResults($event->getHookCallResults(), '  ');
        $this->writeln();

        $this->scenarioStats[$event->getResultCode()]++;
        if (TestResult::FAILED === $event->getResultCode()) {
            $feature = $event->getFeature();
            $scenario = $event->getScenario();
            $this->failedScenarioPaths[] = sprintf('%s:%s', $feature->getFile(), $scenario->getLine());
        }

        $this->currentScenario = null;
        $this->currentBeforeStepEvents = array();
        $this->currentAfterStepEvents = array();
    }

    public function printOutlineHeader(OutlineTested $event)
    {
        $this->inOutline = true;
        $this->currentScenario = $event->getOutline();

        $this->calculateCurrentMaxLineLength($event->getOutline(), $event->getFeature()->getBackground());
        if ($event->getFeature()->hasBackground() && !$this->backgroundPrinted) {
            return;
        }

        $this->printFullScenarioHeader($event->getOutline(), $event->getFeature());
    }

    public function printOutlineFooter()
    {
        $this->writeln();

        $this->inOutline = false;
        $this->currentScenario = null;
        $this->currentBeforeStepEvents = array();
        $this->currentAfterStepEvents = array();
    }

    public function printExamplesHeader(ExampleTested $event)
    {
        $example = $event->getScenario();
        $outline = $this->currentScenario;
        $index = array_search($example, $outline->getExamples());

        if (0 !== $index) {
            return;
        }

        $this->printExamplesSteps($outline);

        $table = $outline->getExampleTable();
        $this->printExamplesTable($table);
    }

    public function printExamplesSteps(OutlineNode $outline)
    {
        $style = ConsoleOutputPrinter::getStyleForResult(TestResult::SKIPPED);

        foreach ($outline->getSteps() as $step) {
            $definition = $this->currentAfterStepEvents[$step->getLine()]->getTestResult()->hasFoundDefinition()
                ? $this->currentAfterStepEvents[$step->getLine()]->getTestResult()->getSearchResult()->getMatchedDefinition()
                : null;

            $this->printStepBody($step, $style, '    ', $definition);
        }

        $this->writeln();
    }

    public function printExamplesTable(ExampleTableNode $table)
    {
        $style = ConsoleOutputPrinter::getStyleForResult(TestResult::SKIPPED);

        $this->writeln(sprintf('    {+keyword}%s:{-keyword}', $table->getKeyword()));

        $wrap = function ($col) use ($style) { return sprintf('{+%s_param}%s{-%s_param}', $style, $col, $style); };
        $row = $table->getRowAsStringWithWrappedValues(0, $wrap);

        $this->writeln(sprintf('      %s', $row));
    }

    public function printExampleRowHeader(ExampleTested $event)
    {
        $this->currentExampleEvent = $event;
    }

    public function printExampleRow(ExampleTested $event)
    {
        $example = $event->getScenario();

        $this->printBeforeHookCallResults($this->currentExampleEvent->getHookCallResults(), '      ');

        if ($this->getParameter('expand')) {
            $this->printExpandedExample($example);
        } else {
            $this->printSimpleExample($example);
        }

        $this->printAfterHookCallResults($event->getHookCallResults(), '      ');

        if (TestResult::FAILED === $event->getResultCode()) {
            $feature = $event->getFeature();
            $scenario = $event->getScenario();
            $this->failedScenarioPaths[] = sprintf('%s:%s', $feature->getFile(), $scenario->getLine());
        }
        $this->scenarioStats[$event->getResultCode()]++;

        $this->currentExampleEvent = null;
        $this->currentBeforeStepEvents = array();
        $this->currentAfterStepEvents = array();
    }

    public function printExpandedExample(ExampleNode $example)
    {
        $this->writeln();
        $this->writeln(sprintf('      %s', $example->getTitle()));

        foreach ($this->currentAfterStepEvents as $i => $afterEvent) {
            if (!in_array($afterEvent->getStep(), $example->getSteps())
                && TestResult::FAILED !== $afterEvent->getResultCode()
                && !$afterEvent->getHookCallResults()->hasExceptions()
                && !$afterEvent->getHookCallResults()->hasStdOuts()
            ) {
                continue;
            }

            $this->printStep($this->currentBeforeStepEvents[$i], $afterEvent, '        ');
        }
    }

    public function printSimpleExample(ExampleNode $example)
    {
        $outline = $this->currentScenario;
        $index = array_search($example, $outline->getExamples());

        $row = $outline->getExampleTable()->getRowAsStringWithWrappedValues(
            $index + 1, array($this, 'colorExampleRowColumn')
        );

        $this->writeln(sprintf('      %s', $row));

        foreach ($this->currentAfterStepEvents as $afterEvent) {
            $this->printStepOutputOrException($afterEvent->getTestResult(), '      ');
        }
    }

    public function colorExampleRowColumn($value, $column)
    {
        $outline = $this->currentScenario;
        $resultCode = TestResult::PASSED;

        foreach ($this->currentAfterStepEvents as $event) {
            if ($this->inBackground) {
                continue;
            }

            $index = array_search($event->getStep(), $this->currentExampleEvent->getScenario()->getSteps());
            $header = $outline->getExampleTable()->getRow(0);
            $steps = $outline->getSteps();
            $outlineStepText = $steps[$index]->getText();

            if (false !== strpos($outlineStepText, '<' . $header[$column] . '>')) {
                $resultCode = max($resultCode, $event->getResultCode());
            }
        }

        $style = ConsoleOutputPrinter::getStyleForResult($resultCode);

        return sprintf('{+%s}%s{-%s}', $style, $value, $style);
    }

    public function printBackgroundHeader(BackgroundTested $event)
    {
        $this->inBackground = true;

        if ($this->backgroundPrinted) {
            return;
        }

        $this->printScenarioTitle($event->getBackground(), $event->getFeature());
    }

    public function printBackgroundFooter(BackgroundTested $event)
    {
        $this->inBackground = false;

        if ($this->backgroundPrinted) {
            return;
        }

        $this->writeln();
        $this->backgroundPrinted = true;
        $this->printFullScenarioHeader($this->currentScenario, $event->getFeature());
    }

    public function printStepHeader(StepTested $event)
    {
        $this->currentBeforeStepEvents[$event->getStep()->getLine()] = $event;
    }

    public function printStepFooter(StepTested $event)
    {
        $this->currentAfterStepEvents[$event->getStep()->getLine()] = $event;
        $this->stepStats[$event->getResultCode()]++;

        if ($this->inBackground && $this->backgroundPrinted
            && TestResult::FAILED !== $event->getResultCode()
            && !$event->getHookCallResults()->hasExceptions()
            && !$event->getHookCallResults()->hasStdOuts()
        ) {
            return;
        }
        if ($this->inOutline && !($this->inBackground && !$this->backgroundPrinted)) {
            return;
        }

        $this->printStep(end($this->currentBeforeStepEvents), end($this->currentAfterStepEvents), '    ');
    }

    public function printStep(StepTested $beforeEvent, StepTested $afterEvent, $lpad)
    {
        $this->printBeforeHookCallResults($beforeEvent->getHookCallResults(), $lpad);

        $step = $afterEvent->getStep();
        $style = ConsoleOutputPrinter::getStyleForResult($afterEvent->getResultCode());
        $definition = $afterEvent->getTestResult()->hasFoundDefinition()
            ? $afterEvent->getTestResult()->getSearchResult()->getMatchedDefinition()
            : null;

        $this->printStepBody($step, $style, $lpad, $definition);
        $this->printStepOutputOrException($afterEvent->getTestResult(), $lpad);
        $this->printAfterHookCallResults($afterEvent->getHookCallResults(), $lpad);
    }

    public function printStepBody(StepNode $step, $style, $lpad, Definition $definition = null)
    {
        $type = $step->getType();
        $text = $step->getText();

        $combinedStringLength = mb_strlen(sprintf('%s%s %s', $lpad, $type, $text), 'utf8');

        if ($definition) {
            $text = $this->colorizeDefinitionArguments($text, $definition, $style);
        }

        $this->write(sprintf('%s{+%s}%s %s{-%s}', $lpad, $style, $type, $text, $style));

        if ($definition && $this->getParameter('paths')) {
            $this->write(
                sprintf(
                    '%s {+comment}# %s{-comment}',
                    str_pad('', $this->currentMaxLineLength - $combinedStringLength),
                    $this->relativizePath($definition->getPath())
                )
            );
        }
        $this->writeln();

        $pad = function ($line) use ($lpad) { return $lpad . '  ' . $line; };
        foreach ($step->getArguments() as $argument) {
            if (!$this->getParameter('multiline')) {
                $this->writeln(sprintf('{+%s}%s{-%s}', $style, $pad('...'), $style));

                continue;
            }

            if ($argument instanceof PyStringNode) {
                $text = '"""' . "\n" . $argument . "\n" . '"""';
            } elseif ($argument instanceof TableNode) {
                $text = (string) $argument;
            }

            $this->writeln(sprintf('{+%s}%s{-%s}', $style, implode("\n", array_map($pad, explode("\n", $text))), $style));
        }
    }

    public function printStepOutputOrException(StepTestResult $testResult, $lpad)
    {
        $callResult = $testResult->getCallResult();

        if ($testResult->hasSearchException()) {
            $exception = $this->presentException($testResult->getSearchException());
            $pad = function ($line) use ($lpad) { return sprintf('%s  {+exception}%s{-exception}', $lpad, $line); };
            $this->writeln(implode("\n", array_map($pad, explode("\n", $exception))));
        }

        if (!$callResult) {
            return;
        }

        if ($callResult->hasStdOut()) {
            $pad = function ($line) use ($lpad) { return sprintf('%s  │ {+stdout}%s{-stdout}', $lpad, $line); };
            $this->writeln(implode("\n", array_map($pad, explode("\n", $callResult->getStdOut()))));
        }

        if ($callResult->hasException()) {
            $style = $callResult->getException() instanceof PendingException
                ? ConsoleOutputPrinter::getStyleForResult(TestResult::PENDING)
                : 'exception';

            $exception = $this->presentException($callResult->getException());
            $pad = function ($line) use (
                $lpad,
                $style
            ) {
                return sprintf('%s  {+%s}%s{-%s}', $lpad, $style, $line, $style);
            };
            $this->writeln(implode("\n", array_map($pad, explode("\n", $exception))));
        }
    }

    public function printBeforeHookCallResults(CallResults $hookCallResults, $lpad)
    {
        if (!$hookCallResults->hasStdOuts() && !$hookCallResults->hasExceptions()) {
            return;
        }

        foreach ($hookCallResults as $callResult) {
            if (!$callResult->hasStdOut() && !$callResult->hasException()) {
                continue;
            }

            $resultCode = $callResult->hasException() ? TestResult::FAILED : TestResult::PASSED;
            $style = ConsoleOutputPrinter::getStyleForResult($resultCode);
            $hook = $callResult->getCall()->getCallee();

            $this->writeln(sprintf('%s┌─ {+%s}@%s{-%s} {+comment}# %s{-comment}', $lpad, $style, $hook, $style, $hook->getPath()));
            $this->writeln(sprintf('%s│', $lpad));

            $this->printHookCallResult($callResult, $lpad);
        }
    }

    public function printAfterHookCallResults(CallResults $hookCallResults, $lpad)
    {
        if (!$hookCallResults->hasStdOuts() && !$hookCallResults->hasExceptions()) {
            return;
        }

        $pad = false;
        foreach ($hookCallResults as $callResult) {
            if (!$callResult->hasStdOut() && !$callResult->hasException()) {
                continue;
            }

            $this->writeln(sprintf('%s│', $lpad));
            $this->printHookCallResult($callResult, $lpad);

            $resultCode = $callResult->hasException() ? TestResult::FAILED : TestResult::PASSED;
            $style = ConsoleOutputPrinter::getStyleForResult($resultCode);
            $hook = $callResult->getCall()->getCallee();

            $this->writeln(sprintf('%s└─ {+%s}@%s{-%s} {+comment}# %s{-comment}', $lpad, $style, $hook, $style, $hook->getPath()));

            if ($callResult->getCall()->getCallee() instanceof AfterFeature) {
                $pad = true;
            }
            if ($callResult->getCall()->getCallee() instanceof AfterScenario) {
                $pad = true;
            }
        }

        if ($pad) {
            $this->writeln();
        }
    }

    public function printHookCallResult(CallResult $callResult, $lpad)
    {
        if ($callResult->hasStdOut()) {
            $pad = function ($line) use ($lpad) { return sprintf('%s│  {+stdout}%s{-stdout}', $lpad, $line); };
            $this->writeln(implode("\n", array_map($pad, explode("\n", $callResult->getStdOut()))));
            $this->writeln(sprintf('%s│', $lpad));
        }

        if ($callResult->hasException()) {
            $pad = function ($l) use ($lpad) { return sprintf('%s╳  {+exception}%s{-exception}', $lpad, $l); };
            $exception = $this->presentException($callResult->getException());
            $this->writeln(implode("\n", array_map($pad, explode("\n", $exception))));
            $this->writeln(sprintf('%s│', $lpad));
        }
    }

    public function printCounters()
    {
        if (count($this->failedScenarioPaths)) {
            $style = ConsoleOutputPrinter::getStyleForResult(TestResult::FAILED);
            $this->writeln(sprintf('--- {+%s}%s{-%s}' . PHP_EOL, $style, $this->translate('failed_scenarios_title'), $style));
            foreach ($this->failedScenarioPaths as $path) {
                $this->writeln(sprintf('    {+%s}%s{-%s}', $style, $this->relativizePath($path), $style));
            }

            $this->writeln();
        }

        $scenariosCount = array_sum(array_values($this->scenarioStats));
        $details = array();
        foreach ($this->scenarioStats as $resultCode => $count) {
            if (0 == $count) {
                continue;
            }

            $style = ConsoleOutputPrinter::getStyleForResult($resultCode);
            $transId = TestResult::codeToString($resultCode) . '_count';
            $message = $this->translateChoice($transId, $count, array('%1%' => $count));
            $details[] = sprintf('{+%s}%s{-%s}', $style, $message, $style);
        }
        $this->write($this->translateChoice('scenarios_count', $scenariosCount, array('%1%' => $scenariosCount)));
        if (count($details)) {
            $this->write(sprintf(' (%s)', implode(', ', $details)));
        }
        $this->writeln();

        $stepsCount = array_sum(array_values($this->stepStats));
        $details = array();
        foreach ($this->stepStats as $resultCode => $count) {
            if (0 == $count) {
                continue;
            }

            $style = ConsoleOutputPrinter::getStyleForResult($resultCode);
            $transId = TestResult::codeToString($resultCode) . '_count';
            $message = $this->translateChoice($transId, $count, array('%1%' => $count));
            $details[] = sprintf('{+%s}%s{-%s}', $style, $message, $style);
        }
        $this->write($this->translateChoice('steps_count', $stepsCount, array('%1%' => $stepsCount)));
        if (count($details)) {
            $this->write(sprintf(' (%s)', implode(', ', $details)));
        }

        $this->writeln();

        if (!$this->getParameter('timer')) {
            return;
        }

        $this->timer->stop();
        $memoryUsage = new MemoryUsage();

        $this->writeln(sprintf('%s (%s)', $this->timer, $memoryUsage));
    }

    public function colorizeDefinitionArguments($text, Definition $definition, $style)
    {
        $regex = $this->patternTransformer->toRegex($definition->getPattern());
        $paramStyle = $style . '_param';

        // If it's just a string - skip
        if ('/' !== substr($regex, 0, 1)) {
            return $text;
        }

        // Find arguments with offsets
        $matches = array();
        preg_match($regex, $text, $matches, PREG_OFFSET_CAPTURE);
        array_shift($matches);

        // Replace arguments with colorized ones
        $shift = 0;
        $lastReplacementPosition = 0;
        foreach ($matches as $key => $match) {
            if (!is_numeric($key) || -1 === $match[1] || false !== strpos($match[0], '<')) {
                continue;
            }

            $offset = $match[1] + $shift;
            $value = $match[0];

            // Skip inner matches
            if ($lastReplacementPosition > $offset) {
                continue;
            }
            $lastReplacementPosition = $offset + strlen($value);

            $begin = substr($text, 0, $offset);
            $end = substr($text, $lastReplacementPosition);
            $format = "{-$style}{+$paramStyle}%s{-$paramStyle}{+$style}";
            $text = sprintf("%s{$format}%s", $begin, $value, $end);

            // Keep track of how many extra characters are added
            $shift += strlen($format) - 2;
            $lastReplacementPosition += strlen($format) - 2;
        }

        // Replace "<", ">" with colorized ones
        $text = preg_replace(
            '/(<[^>]+>)/',
            "{-$style}{+$paramStyle}\$1{-$paramStyle}{+$style}",
            $text
        );

        return $text;
    }

    /**
     * @param ScenarioInterface   $scenario
     * @param null|BackgroundNode $background
     */
    private function calculateCurrentMaxLineLength(ScenarioInterface $scenario, BackgroundNode $background = null)
    {
        $this->currentMaxLineLength = 0;

        if ($background) {
            $titleLines = explode("\n", $background->getTitle());

            $this->currentMaxLineLength = max(
                $this->currentMaxLineLength,
                mb_strlen($background->getKeyword(), 'utf8') + mb_strlen(current($titleLines), 'utf8') + 4
            );

            foreach ($background->getSteps() as $step) {
                $this->currentMaxLineLength = max(
                    $this->currentMaxLineLength,
                    mb_strlen($step->getType(), 'utf8') + mb_strlen($step->getText(), 'utf8') + 5
                );
            }
        }

        $titleLines = explode("\n", $scenario->getTitle());

        $this->currentMaxLineLength = max(
            $this->currentMaxLineLength,
            mb_strlen($scenario->getKeyword(), 'utf8') + mb_strlen(current($titleLines), 'utf8') + 4
        );

        foreach ($scenario->getSteps() as $step) {
            $this->currentMaxLineLength = max(
                $this->currentMaxLineLength,
                mb_strlen($step->getType(), 'utf8') + mb_strlen($step->getText(), 'utf8') + 5
            );
        }
    }

    private function relativizePath($path)
    {
        return str_replace($this->basePath . DIRECTORY_SEPARATOR, '', $path);
    }
}

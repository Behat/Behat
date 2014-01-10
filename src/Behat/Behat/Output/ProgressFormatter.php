<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Output;

use Behat\Behat\Definition\Pattern\PatternTransformer;
use Behat\Behat\Output\Printer\ConsoleOutputPrinter;
use Behat\Behat\Tester\Event\ExampleTested;
use Behat\Behat\Tester\Event\FeatureTested;
use Behat\Behat\Tester\Event\ScenarioTested;
use Behat\Behat\Tester\Event\StepTested;
use Behat\Behat\Tester\Result\TestResult;
use Behat\Testwork\Counter\MemoryUsage;
use Behat\Testwork\Counter\Timer;
use Behat\Testwork\Exception\ExceptionPresenter;
use Behat\Testwork\Hook\Event\HookableEvent;
use Behat\Testwork\Hook\Event\LifecycleEvent;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Output\TranslatableCliFormatter;
use Behat\Testwork\Tester\Event\ExerciseCompleted;
use Behat\Testwork\Tester\Event\SuiteTested;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Behat progress formatter.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ProgressFormatter extends TranslatableCliFormatter
{
    /**
     * @var PatternTransformer
     */
    private $patternTransformer;
    /**
     * @var array
     */
    private $scenarioStats;
    /**
     * @var array
     */
    private $stepStats;
    /**
     * @var integer
     */
    private $stepsPrinted = 0;
    /**
     * @var string[]
     */
    private $failedHookPaths = array();
    /**
     * @var string[]
     */
    private $failedScenarioPaths = array();
    /**
     * @var array[]
     */
    private $failedStepPaths = array();
    /**
     * @var array[]
     */
    private $pendingStepPaths = array();
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

        $this->setParameter('timer', true);
    }

    public static function getSubscribedEvents()
    {
        return array(
            ExerciseCompleted::BEFORE => array('startExerciseTimer', 999),
            ExerciseCompleted::AFTER  => array('printStatistics', -50),
            SuiteTested::BEFORE       => array('collectFailedHooks', 999),
            SuiteTested::AFTER        => array('collectFailedHooks', 999),
            FeatureTested::BEFORE     => array('collectFailedHooks', 999),
            FeatureTested::AFTER      => array('collectFailedHooks', 999),
            ScenarioTested::BEFORE    => array('collectFailedHooks', 999),
            ScenarioTested::AFTER     => array(array('collectScenarioStats', 999), array('collectFailedHooks', 999)),
            ExampleTested::BEFORE     => array('collectFailedHooks', 999),
            ExampleTested::AFTER      => array(array('collectScenarioStats', 999), array('collectFailedHooks', 999)),
            StepTested::BEFORE        => array('collectFailedHooks', 999),
            StepTested::AFTER         => array(array('printStepCharacter', -50), array('collectFailedHooks', 999)),
        );
    }

    public function getName()
    {
        return 'progress';
    }

    public function getDescription()
    {
        return 'Prints one character per step.';
    }

    public function startExerciseTimer()
    {
        $this->timer = new Timer();
        $this->timer->start();
    }

    public function printStepCharacter(StepTested $event)
    {
        $resultCode = $event->getResultCode();

        switch ($resultCode) {
            case TestResult::PASSED:
                $this->write('{+passed}.{-passed}');
                break;
            case TestResult::SKIPPED:
                $this->write('{+skipped}-{-skipped}');
                break;
            case TestResult::PENDING:
                $this->write('{+pending}P{-pending}');
                break;
            case TestResult::UNDEFINED:
                $this->write('{+undefined}U{-undefined}');
                break;
            case TestResult::FAILED:
                $this->write('{+failed}F{-failed}');
                break;
        }

        if (++$this->stepsPrinted % 70 == 0) {
            $this->writeln(' ' . $this->stepsPrinted);
        }

        $this->stepStats[$event->getResultCode()]++;
        if (TestResult::FAILED == $event->getResultCode()) {
            $text = sprintf('%s %s', $event->getStep()->getType(), $event->getStep()->getText());
            $path = sprintf(
                '%s:%d',
                $this->relativizePath($event->getFeature()->getFile()),
                $event->getStep()->getLine()
            );
            $error = $event->getTestResult()->hasException() ? $this->presentException(
                $event->getTestResult()->getException()
            ) : null;
            $stdOut = null;

            if ($event->getTestResult()->getHookCallResults()->hasExceptions()) {
                $error = null;
            } else {
                $stdOut = !$event->getTestResult()->hasSearchException()
                    ? $event->getTestResult()->getCallResult()->getStdOut()
                    : null;
            }

            $this->failedStepPaths[] = array($text, $path, $error, $stdOut);
        }

        if (TestResult::PENDING == $event->getResultCode()) {
            $text = sprintf('%s %s', $event->getStep()->getType(), $event->getStep()->getText());
            $path = $this->relativizePath(
                $event->getTestResult()->getSearchResult()->getMatchedDefinition()->getPath()
            );
            $exception = $event->getTestResult()->getCallResult()->getException();
            $error = $this->presentException($exception);

            $this->pendingStepPaths[] = array($text, $path, $error);
        }
    }

    public function collectFailedHooks(HookableEvent $event)
    {
        if (!$event->getHookCallResults()->hasExceptions()) {
            return;
        }

        foreach ($event->getHookCallResults() as $result) {
            if ($result->hasException()) {
                $hook = (string) $result->getCall()->getCallee();
                $path = $result->getCall()->getCallee()->getPath();
                $error = $this->presentException($result->getException());
                $stdOut = $result->getStdOut();

                $this->failedHookPaths[] = array($hook, $path, $error, $stdOut);
            }
        }
    }

    public function collectScenarioStats(LifecycleEvent $event)
    {
        $this->scenarioStats[$event->getResultCode()]++;
        if (TestResult::FAILED === $event->getResultCode()) {
            $feature = $event->getFeature();
            $scenario = $event->getScenario();
            $this->failedScenarioPaths[] = sprintf(
                '%s:%s',
                $this->relativizePath($feature->getFile()),
                $scenario->getLine()
            );
        }
    }

    public function printStatistics()
    {
        $this->writeln(PHP_EOL);
        $this->printCounters();
    }

    public function printCounters()
    {
        $this->printFailedHookPaths();
        $this->printFailedStepPaths();
        $this->printPendingStepPaths();

        $this->printScenarioStats();
        $this->printStepStats();

        if (!$this->getParameter('timer')) {
            return;
        }

        $this->timer->stop();
        $memoryUsage = new MemoryUsage();

        $this->writeln(sprintf('%s (%s)', $this->timer, $memoryUsage));
    }

    public function printFailedHookPaths()
    {
        if (!count($this->failedHookPaths)) {
            return;
        }

        $style = ConsoleOutputPrinter::getStyleForResult(TestResult::FAILED);
        $this->writeln(sprintf('--- {+%s}%s{-%s}' . PHP_EOL, $style, $this->translate('failed_hooks_title'), $style));
        foreach ($this->failedHookPaths as $info) {
            list($hook, $path, $exception, $stdOut) = $info;

            $this->writeln(sprintf('    {+%s}%s{-%s} {+comment}# %s{-comment}', $style, $hook, $style, $path));

            $pad = function ($line) {
                return '      ' . $line;
            };

            if (null !== $stdOut) {
                $padText = function ($line) {
                    return '      │ ' . $line;
                };
                $this->writeln(implode("\n", array_map($padText, explode("\n", $stdOut))));
            }

            if ($exception) {
                $this->writeln(
                    sprintf('{+%s}%s{-%s}', $style, implode("\n", array_map($pad, explode("\n", $exception))), $style)
                );
            }

            $this->writeln();
        }
    }

    public function printFailedStepPaths()
    {
        if (!count($this->failedStepPaths)) {
            return;
        }

        $style = ConsoleOutputPrinter::getStyleForResult(TestResult::FAILED);
        $this->writeln(sprintf('--- {+%s}%s{-%s}' . PHP_EOL, $style, $this->translate('failed_steps_title'), $style));
        foreach ($this->failedStepPaths as $i => $info) {
            list($text, $path, $exception, $stdOut) = $info;

            $scenarioPath = $this->failedScenarioPaths[$i];
            $this->writeln(sprintf('    {+%s}%s{-%s}', $style, $scenarioPath, $style));

            $this->writeln(sprintf('      {+%s}%s{-%s} {+comment}# %s{-comment}', $style, $text, $style, $path));

            $pad = function ($line) {
                return '        ' . $line;
            };

            if (null !== $stdOut) {
                $padText = function ($line) {
                    return '        │ ' . $line;
                };
                $this->writeln(implode("\n", array_map($padText, explode("\n", $stdOut))));
            }

            if ($exception) {
                $this->writeln(
                    sprintf('{+%s}%s{-%s}', $style, implode("\n", array_map($pad, explode("\n", $exception))), $style)
                );
            }

            $this->writeln();
        }
    }

    public function printPendingStepPaths()
    {
        if (!count($this->pendingStepPaths)) {
            return;
        }

        $style = ConsoleOutputPrinter::getStyleForResult(TestResult::PENDING);
        $this->writeln(sprintf('--- {+%s}%s{-%s}' . PHP_EOL, $style, $this->translate('pending_steps_title'), $style));
        foreach ($this->pendingStepPaths as $info) {
            list($text, $path, $exception) = $info;

            $this->writeln(sprintf('    {+%s}%s{-%s} {+comment}# %s{-comment}', $style, $text, $style, $path));

            $pad = function ($line) {
                return '      ' . $line;
            };
            $this->writeln(
                sprintf('{+%s}%s{-%s}', $style, implode("\n", array_map($pad, explode("\n", $exception))), $style)
            );

            $this->writeln();
        }
    }

    public function printScenarioStats()
    {
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
    }

    protected function printStepStats()
    {
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
    }

    private function relativizePath($path)
    {
        return str_replace($this->basePath . DIRECTORY_SEPARATOR, '', $path);
    }
}

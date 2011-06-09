<?php

namespace Behat\Behat\Formatter;

use Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\EventDispatcher\Event;

use Behat\Behat\Definition\Definition,
    Behat\Behat\DataCollector\LoggerDataCollector,
    Behat\Behat\Exception\Pending,
    Behat\Behat\Event\SuiteEvent,
    Behat\Behat\Event\StepEvent;

use Behat\Gherkin\Node\BackgroundNode,
    Behat\Gherkin\Node\StepNode;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Progress formatter.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ProgressFormatter extends ConsoleFormatter
{
    /**
     * Maximum line length.
     *
     * @var     integer
     */
    protected $maxLineLength = 0;

    /**
     * {@inheritdoc}
     */
    protected function getDefaultParameters()
    {
        return array();
    }

    /**
     * @see     Symfony\Component\EventDispatcher\EventSubscriberInterface::getSubscribedEvents()
     */
    public static function getSubscribedEvents()
    {
        $events = array('afterSuite', 'afterStep');

        return array_combine($events, $events);
    }

    /**
     * Listens to "suite.after" event.
     *
     * @param   Behat\Behat\Event\SuiteEvent    $event
     *
     * @uses    printFailedSteps()
     * @uses    printPendingSteps()
     * @uses    printSummary()
     * @uses    printUndefinedStepsSnippets()
     */
    public function afterSuite(SuiteEvent $event)
    {
        $logger = $event->getLogger();

        $this->writeln("\n");
        $this->printFailedSteps($logger);
        $this->printPendingSteps($logger);
        $this->printSummary($logger);
        $this->printUndefinedStepsSnippets($logger);
    }

    /**
     * Listens to "step.after" event.
     *
     * @param   Behat\Behat\Event\StepEvent $event
     *
     * @uses    printStep()
     */
    public function afterStep(StepEvent $event)
    {
        $this->printStep(
            $event->getStep(),
            $event->getResult(),
            $event->getDefinition(),
            $event->getSnippet(),
            $event->getException()
        );
    }

    /**
     * Prints step.
     *
     * @param   Behat\Gherkin\Node\StepNode         $step           step node
     * @param   integer                             $result         step result code
     * @param   Behat\Behat\Definition\Definition   $definition     definition instance (if step defined)
     * @param   string                              $snippet        snippet (if step is undefined)
     * @param   Exception                           $exception      exception (if step is failed)
     *
     * @uses    Behat\Behat\Tester\StepTester
     */
    protected function printStep(StepNode $step, $result, Definition $definition = null,
                                 $snippet = null, \Exception $exception = null)
    {
        switch ($result) {
            case StepEvent::PASSED:
                $this->write('{+passed}.{-passed}');
                break;
            case StepEvent::SKIPPED:
                $this->write('{+skipped}-{-skipped}');
                break;
            case StepEvent::PENDING:
                $this->write('{+pending}P{-pending}');
                break;
            case StepEvent::UNDEFINED:
                $this->write('{+undefined}U{-undefined}');
                break;
            case StepEvent::FAILED:
                $this->write('{+failed}F{-failed}');
                break;
        }
    }

    /**
     * Prints all failed steps info.
     *
     * @param   Behat\Behat\DataCollector\LoggerDataCollector   $logger suite logger
     */
    protected function printFailedSteps(LoggerDataCollector $logger)
    {
        if (count($logger->getFailedStepsEvents())) {
            $header = $this->translate('failed steps');
            $this->writeln("{+failed}(::) $header (::){-failed}\n");
            $this->printExceptionEvents($logger->getFailedStepsEvents());
        }
    }

    /**
     * Prints all pending steps information.
     *
     * @param   Behat\Behat\DataCollector\LoggerDataCollector   $logger suite logger
     */
    protected function printPendingSteps(LoggerDataCollector $logger)
    {
        if (count($logger->getPendingStepsEvents())) {
            $header = $this->translate('pending steps');
            $this->writeln("{+pending}(::) $header (::){-pending}\n");
            $this->printExceptionEvents($logger->getPendingStepsEvents());
        }
    }

    /**
     * Prints exceptions information.
     *
     * @param   array   $events failed step events
     */
    protected function printExceptionEvents(array $events)
    {
        foreach ($events as $number => $event) {
            $exception = $event->getException();

            if (null !== $exception) {
                $color = $exception instanceof Pending ? 'pending' : 'failed';

                if ($this->parameters->get('verbose')) {
                    $error = (string) $exception;
                } else {
                    $error = $exception->getMessage();
                }
                $error = sprintf("%s. %s",
                    str_pad((string) ($number + 1), 2, '0', STR_PAD_LEFT),
                    strtr($error, array("\n" => "\n    "))
                );
                $error = $this->relativizePathsInString($error);

                $this->writeln("{+$color}$error{-$color}");
            }

            $this->printStepPath($event->getStep(), $event->getDefinition(), $exception);
        }
    }

    /**
     * Prints path to step.
     *
     * @param   Behat\Gherkin\Node\StepNode         $step           step node
     * @param   Behat\Behat\Definition\Definition   $definition     definition (if step defined)
     * @param   Exception                           $exception      exception (if step failed)
     */
    protected function printStepPath(StepNode $step, Definition $definition = null,
                                     \Exception $exception = null)
    {
        $color      = $exception instanceof Pending ? 'pending' : 'failed';
        $type       = $step->getType();
        $text       = $step->getText();
        $stepPath   = "In step `$type $text'.";
        $stepPathLn = mb_strlen($stepPath);

        $node = $step->getParent();
        if ($node instanceof BackgroundNode) {
            $scenarioPath   = "From scenario background.";
        } else {
            $title          = $node->getTitle();
            $title          = $title ? "`$title'" : '***';
            $scenarioPath   = "From scenario $title.";
        }
        $scenarioPathLn     = mb_strlen($scenarioPath);

        $this->maxLineLength = max($this->maxLineLength, $stepPathLn);
        $this->maxLineLength = max($this->maxLineLength, $scenarioPathLn);

        $this->write("    {+$color}$stepPath{-$color}");
        if (null !== $definition) {
            $indentCount = $this->maxLineLength - $stepPathLn;
            $this->printPathComment($definition->getFile(), $definition->getLine(), $indentCount);
        } else {
            $this->writeln();
        }

        $this->write("    {+$color}$scenarioPath{-$color}");
        $indentCount = $this->maxLineLength - $scenarioPathLn;
        $this->printPathComment($node->getFile(), $node->getLine(), $indentCount);
        $this->writeln();
    }

    /**
     * Prints summary suite run information.
     *
     * @param   Behat\Behat\DataCollector\LoggerDataCollector   $logger suite logger
     */
    protected function printSummary(LoggerDataCollector $logger)
    {
        $this->printScenariosSummary($logger);
        $this->printStepsSummary($logger);

        if ($this->parameters->get('time')) {
            $this->printTimeSummary($logger);
        }
    }

    /**
     * Prints scenarios summary information.
     *
     * @param   Behat\Behat\DataCollector\LoggerDataCollector   $logger suite logger
     */
    protected function printScenariosSummary(LoggerDataCollector $logger)
    {
        $count  = $logger->getScenariosCount();
        $header = $this->translateChoice(
            '{0} No scenarios|{1} 1 scenario|]1,Inf] %1% scenarios', $count, array('%1%' => $count)
        );
        $this->write($header);
        $this->printStatusesSummary($logger->getScenariosStatuses());
    }

    /**
     * Prints steps summary information.
     *
     * @param   Behat\Behat\DataCollector\LoggerDataCollector   $logger suite logger
     */
    protected function printStepsSummary(LoggerDataCollector $logger)
    {
        $count  = $logger->getStepsCount();
        $header = $this->translateChoice(
            '{0} No steps|{1} 1 step|]1,Inf] %1% steps', $count, array('%1%' => $count)
        );
        $this->write($header);
        $this->printStatusesSummary($logger->getStepsStatuses());
    }

    /**
     * Prints statuses summary.
     *
     * @param   array   $statusesStatistics statuses statistic hash (status => count)
     */
    protected function printStatusesSummary(array $statusesStatistics)
    {
        $statuses = array();
        foreach ($statusesStatistics as $status => $count) {
            if ($count) {
                $transStatus = $this->translateChoice(
                    "[1,Inf] %1% $status", $count, array('%1%' => $count)
                );
                $statuses[] = "{+$status}$transStatus{-$status}";
            }
        }
        $this->writeln(count($statuses) ? ' ' . sprintf('(%s)', implode(', ', $statuses)) : '');
    }

    /**
     * Prints suite run time inforamtion.
     *
     * @param   Behat\Behat\DataCollector\LoggerDataCollector   $logger suite logger
     */
    protected function printTimeSummary(LoggerDataCollector $logger)
    {
        $time       = $logger->getTotalTime();
        $minutes    = floor($time / 60);
        $seconds    = round($time - ($minutes * 60), 3);

        $this->writeln($minutes . 'm' . $seconds . 's');
    }

    /**
     * Prints undefined steps snippets.
     *
     * @param   Behat\Behat\DataCollector\LoggerDataCollector   $logger suite logger
     */
    protected function printUndefinedStepsSnippets(LoggerDataCollector $logger)
    {
        if (count($logger->getDefinitionsSnippets())) {
            $header = $this->translate(
                'You can implement step definitions for undefined steps with these snippets:'
            );
            $this->writeln("\n{+undefined}$header{-undefined}\n");

            foreach ($logger->getDefinitionsSnippets() as $key => $snippet) {
                $this->writeln("{+undefined}$snippet{-undefined}\n");
            }
        }
    }

    /**
     * Prints path comment.
     *
     * @param   string  $file           filename
     * @param   integer $line           line number
     * @param   integer $indentCount    indenation number
     */
    protected function printPathComment($file, $line, $indentCount = 0)
    {
        $indent = str_repeat(' ', $indentCount);
        $file = $this->relativizePathsInString($file);

        $this->writeln("$indent {+comment}# $file:$line{-comment}");
    }

    /**
     * Returns string with relativized paths.
     *
     * @param   string  $string
     *
     * @return  string
     */
    protected function relativizePathsInString($string)
    {
        if ($basePath = $this->parameters->get('base_path')) {
            $basePath = realpath($basePath) . DIRECTORY_SEPARATOR;
            $string = str_replace($basePath, '', $string);
        }

        return $string;
    }
}

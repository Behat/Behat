<?php

namespace Behat\Behat\Formatter;

use Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\EventDispatcher\Event;

use Behat\Behat\Definition\DefinitionInterface,
    Behat\Behat\DataCollector\LoggerDataCollector,
    Behat\Behat\Exception\PendingException,
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
    public static function getDescription()
    {
        return "Prints one character per step.";
    }

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
     * @param   Behat\Gherkin\Node\StepNode                 $step       step node
     * @param   integer                                     $result     step result code
     * @param   Behat\Behat\Definition\DefinitionInterface  $definition definition instance (if step defined)
     * @param   string                                      $snippet    snippet (if step is undefined)
     * @param   Exception                                   $exception  exception (if step is failed)
     *
     * @uses    Behat\Behat\Tester\StepTester
     */
    protected function printStep(StepNode $step, $result, DefinitionInterface $definition = null,
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
            case StepEvent::UNSTABLE:
                $this->write('{+unstable}*{-unstable}');
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
            $header = $this->translate('failed_steps_title');
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
            $header = $this->translate('pending_steps_title');
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
                $color = $exception instanceof PendingException ? 'pending' : 'failed';

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
     * @param   Behat\Gherkin\Node\StepNode                 $step           step node
     * @param   Behat\Behat\Definition\DefinitionInterface  $definition     definition (if step defined)
     * @param   Exception                                   $exception      exception (if step failed)
     */
    protected function printStepPath(StepNode $step, DefinitionInterface $definition = null,
                                     \Exception $exception = null)
    {
        $color      = $exception instanceof PendingException ? 'pending' : 'failed';
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
            $this->printPathComment(
                $this->relativizePathsInString($definition->getPath()), $indentCount
            );
        } else {
            $this->writeln();
        }

        $this->write("    {+$color}$scenarioPath{-$color}");
        $indentCount = $this->maxLineLength - $scenarioPathLn;
        $this->printPathComment(
            $this->relativizePathsInString($node->getFile()) . ':' . $node->getLine(), $indentCount
        );
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
        $header = $this->translateChoice('scenarios_count', $count, array('%1%' => $count));
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
        $header = $this->translateChoice('steps_count', $count, array('%1%' => $count));
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
                    "{$status}_count", $count, array('%1%' => $count)
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
        if ($this->getParameter('snippets') && count($logger->getDefinitionsSnippets())) {
            $header = $this->translate('proposal_title');
            $this->writeln("\n{+undefined}$header{-undefined}\n");
            $this->printSnippets($logger);
        }
    }

    /**
     * Prints steps snippets.
     *
     * @param   Behat\Behat\DataCollector\LoggerDataCollector   $logger suite logger
     */
    protected function printSnippets(LoggerDataCollector $logger)
    {
        foreach ($logger->getDefinitionsSnippets() as $snippet) {
            $snippetText = $snippet->getSnippet();

            if ($this->getParameter('snippets_paths')) {
                $indent = str_pad(
                    '', mb_strlen($snippetText) - mb_strlen(ltrim($snippetText)), ' '
                );
                $this->writeln("{+undefined}$indent/**{-undefined}");
                foreach ($snippet->getSteps() as $step) {
                    $this->writeln(sprintf(
                        '{+undefined}%s * %s %s # %s:%d{-undefined}', $indent,
                        $step->getType(), $step->getText(),
                        $this->relativizePathsInString($step->getFile()), $step->getLine()
                    ));
                }

                if (false !== mb_strpos($snippetText, '/**')) {
                    $snippetText = str_replace('/**', ' *', $snippetText);
                } else {
                    $this->writeln("{+undefined}$indent */{-undefined}");
                }
            }

            $this->writeln("{+undefined}$snippetText{-undefined}\n");
        }
    }

    /**
     * Prints path comment.
     *
     * @param   string  $path           item path
     * @param   integer $indentCount    indenation number
     */
    protected function printPathComment($path, $indentCount = 0)
    {
        $indent = str_repeat(' ', $indentCount);
        $this->writeln("$indent {+comment}# $path{-comment}");
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

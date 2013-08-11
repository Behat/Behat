<?php

namespace Behat\Behat\Output\Formatter;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Definition\DefinitionInterface;
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Event\ExerciseEvent;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\Exception\PendingException;
use Behat\Behat\Snippet\EventSubscriber\SnippetsCollector;
use Behat\Behat\Tester\EventSubscriber\StatisticsCollector;
use Behat\Gherkin\Node\BackgroundNode;
use Behat\Gherkin\Node\StepNode;
use Exception;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Progress formatter.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ProgressFormatter extends TranslatableConsoleFormatter
{
    /**
     * Maximum line length.
     *
     * @var integer
     */
    protected $maxLineLength = 0;
    /**
     * Holds amount of printed items in current line;
     *
     * @var integer
     */
    private $stepsPrinted = 0;
    /**
     * @var StatisticsCollector
     */
    private $statisticsCollector;
    /**
     * @var SnippetsCollector
     */
    private $snippetsCollector;

    /**
     * Initializes formatter.
     *
     * @param StatisticsCollector $statisticsCollector
     * @param SnippetsCollector   $snippetsCollector
     * @param TranslatorInterface $translator
     */
    public function __construct(
        StatisticsCollector $statisticsCollector,
        SnippetsCollector $snippetsCollector,
        TranslatorInterface $translator
    )
    {
        $this->statisticsCollector = $statisticsCollector;
        $this->snippetsCollector = $snippetsCollector;

        parent::__construct($translator);
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::AFTER_STEP     => array('afterStep', -50),
            EventInterface::AFTER_EXERCISE => array('afterExercise', -50),
        );
    }

    /**
     * Returns formatter name.
     *
     * @return string
     */
    public function getName()
    {
        return 'progress';
    }

    /**
     * Returns formatter description.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Prints one character per step.';
    }

    /**
     * Listens to "suite.after" event.
     *
     * @param ExerciseEvent $event
     *
     * @uses printFailedSteps()
     * @uses printPendingSteps()
     * @uses printSummary()
     * @uses printUndefinedStepsSnippets()
     */
    public function afterExercise(ExerciseEvent $event)
    {
        $this->writeln("\n");
        $this->printFailedSteps($this->statisticsCollector);
        $this->printPendingSteps($this->statisticsCollector);
        $this->printSummary($this->statisticsCollector);
        $this->printUndefinedStepsSnippets($this->snippetsCollector);
    }

    /**
     * Listens to "step.after" event.
     *
     * @param StepEvent $event
     *
     * @uses printStep()
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
     * {@inheritdoc}
     */
    protected function getDefaultParameters()
    {
        return array_merge(
            parent::getDefaultParameters(),
            array()
        );
    }

    /**
     * Prints step.
     *
     * @param StepNode            $step       step node
     * @param integer             $result     step result code
     * @param DefinitionInterface $definition definition instance (if step defined)
     * @param string              $snippet    snippet (if step is undefined)
     * @param Exception           $exception  exception (if step is failed)
     *
     * @uses StepEvent
     */
    protected function printStep(
        StepNode $step,
        $result,
        DefinitionInterface $definition = null,
        $snippet = null,
        Exception $exception = null
    )
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

        if (++$this->stepsPrinted % 70 == 0) {
            $this->writeln(' ' . $this->stepsPrinted);
        }
    }

    /**
     * Prints all failed steps info.
     *
     * @param StatisticsCollector $stats
     */
    protected function printFailedSteps(StatisticsCollector $stats)
    {
        if (count($stats->getFailedStepsEvents())) {
            $header = $this->translate('failed_steps_title');
            $this->writeln("{+failed}(::) $header (::){-failed}\n");
            $this->printExceptionEvents($stats->getFailedStepsEvents());
        }
    }

    /**
     * Prints all pending steps information.
     *
     * @param StatisticsCollector $stats
     */
    protected function printPendingSteps(StatisticsCollector $stats)
    {
        if (count($stats->getPendingStepsEvents())) {
            $header = $this->translate('pending_steps_title');
            $this->writeln("{+pending}(::) $header (::){-pending}\n");
            $this->printExceptionEvents($stats->getPendingStepsEvents());
        }
    }

    /**
     * Prints exceptions information.
     *
     * @param StepEvent[] $events failed step events
     */
    protected function printExceptionEvents(array $events)
    {
        foreach ($events as $number => $event) {
            $exception = $event->getException();

            if (null !== $exception) {
                if ($exception instanceof PendingException) {
                    $color = 'pending';
                    $error = $exception->getMessage();
                } else {
                    $color = 'failed';
                    $error = $this->exceptionToString($exception);
                }

                $error = sprintf("%s. %s",
                    str_pad((string)($number + 1), 2, '0', STR_PAD_LEFT),
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
     * @param StepNode            $step       step node
     * @param DefinitionInterface $definition definition (if step defined)
     * @param \Exception          $exception  exception (if step failed)
     */
    protected function printStepPath(
        StepNode $step,
        DefinitionInterface $definition = null,
        \Exception $exception = null
    )
    {
        $color = $exception instanceof PendingException ? 'pending' : 'failed';
        $type = $step->getType();
        $text = $step->getText();
        $stepPath = "In step `$type $text'.";
        $stepPathLn = mb_strlen($stepPath, 'utf8');

        $node = $step->getParent();
        if ($node instanceof BackgroundNode) {
            $scenarioPath = "From scenario background.";
        } else {
            $title = $node->getTitle();
            $title = $title ? "`$title'" : '***';
            $scenarioPath = "From scenario $title.";
        }
        $scenarioPathLn = mb_strlen($scenarioPath, 'utf8');

        $feature = $node->getFeature();
        $title = $feature->getTitle();
        $title = $title ? "`$title'" : '***';
        $featurePath = "Of feature $title.";
        $featurePathLn = mb_strlen($featurePath, 'utf8');

        $this->maxLineLength = max($this->maxLineLength, $stepPathLn);
        $this->maxLineLength = max($this->maxLineLength, $scenarioPathLn);
        $this->maxLineLength = max($this->maxLineLength, $featurePathLn);

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

        $this->write("    {+$color}$featurePath{-$color}");
        $indentCount = $this->maxLineLength - $featurePathLn;
        $this->printPathComment(
            $this->relativizePathsInString($feature->getFile()), $indentCount
        );

        $this->writeln();
    }

    /**
     * Prints summary suite run information.
     *
     * @param StatisticsCollector $stats
     */
    protected function printSummary(StatisticsCollector $stats)
    {
        $this->printScenariosSummary($stats);
        $this->printStepsSummary($stats);

        if ($this->getParameter('time')) {
            $this->printTimeSummary($stats);
        }
    }

    /**
     * Prints scenarios summary information.
     *
     * @param StatisticsCollector $stats
     */
    protected function printScenariosSummary(StatisticsCollector $stats)
    {
        $count = $stats->getScenariosCount();
        $header = $this->translateChoice('scenarios_count', $count, array('%1%' => $count));
        $this->write($header);
        $this->printStatusesSummary($stats->getScenariosStatuses());
    }

    /**
     * Prints steps summary information.
     *
     * @param StatisticsCollector $stats
     */
    protected function printStepsSummary(StatisticsCollector $stats)
    {
        $count = $stats->getStepsCount();
        $header = $this->translateChoice('steps_count', $count, array('%1%' => $count));
        $this->write($header);
        $this->printStatusesSummary($stats->getStepsStatuses());
    }

    /**
     * Prints statuses summary.
     *
     * @param array $statusesStatistics statuses statistic hash (status => count)
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
     * Prints suite run time information.
     *
     * @param StatisticsCollector $stats
     */
    protected function printTimeSummary(StatisticsCollector $stats)
    {
        $time = $stats->getTotalTime();
        $minutes = floor($time / 60);
        $seconds = round($time - ($minutes * 60), 3);

        $this->writeln($minutes . 'm' . $seconds . 's');
    }

    /**
     * Prints undefined steps snippets.
     *
     * @param SnippetsCollector $snippets
     */
    protected function printUndefinedStepsSnippets(SnippetsCollector $snippets)
    {
        if ($this->getParameter('snippets') && $snippets->hasSnippets()) {
            $header = $this->translate('proposal_title');
            $this->writeln("\n{+undefined}$header{-undefined}\n");
            $this->printSnippets($snippets);
        }
    }

    /**
     * Prints steps snippets.
     *
     * @param SnippetsCollector $snippets
     */
    protected function printSnippets(SnippetsCollector $snippets)
    {
        foreach ($snippets->getSnippets() as $snippet) {
            $snippetText = $snippet->getSnippet();

            if ($this->getParameter('snippets_paths')) {
                $indent = str_pad(
                    '', mb_strlen($snippetText, 'utf8') - mb_strlen(ltrim($snippetText), 'utf8'), ' '
                );
                $this->writeln("{+undefined}$indent/**{-undefined}");
                foreach ($snippets->getStepsThatNeed($snippet) as $step) {
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
     * @param string  $path        item path
     * @param integer $indentCount indentation number
     */
    protected function printPathComment($path, $indentCount = 0)
    {
        $indent = str_repeat(' ', $indentCount);
        $this->writeln("$indent {+comment}# $path{-comment}");
    }

    /**
     * Returns string with relativized paths.
     *
     * @param string $string
     *
     * @return string
     */
    protected function relativizePathsInString($string)
    {
        if ($basePath = $this->getParameter('base_path')) {
            $basePath = realpath($basePath) . DIRECTORY_SEPARATOR;
            $string = str_replace($basePath, '', $string);
        }

        return $string;
    }

    /**
     * @return StatisticsCollector
     */
    protected function getStatisticsCollector()
    {
        return $this->statisticsCollector;
    }

    /**
     * @return SnippetsCollector
     */
    protected function getSnippetsCollector()
    {
        return $this->snippetsCollector;
    }
}

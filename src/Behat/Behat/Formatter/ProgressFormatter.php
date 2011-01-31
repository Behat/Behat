<?php

namespace Behat\Behat\Formatter;

use Symfony\Component\EventDispatcher\Event;

use Behat\Behat\Tester\StepTester,
    Behat\Behat\StepDefinition\Definition,
    Behat\Behat\Statistic\StatisticsCollector,
    Behat\Behat\Exception\Pending;

use Behat\Gherkin\Node\BackgroundNode,
    Behat\Gherkin\Node\StepNode;

class ProgressFormatter extends ConsoleFormatter
{
    protected $maxLineLength = 0;

    protected function getDefaultParameters()
    {
        return array();
    }

    public function afterSuite(Event $event)
    {
        $statistics = $event->getSubject();

        $this->writeln("\n");
        $this->printFailedSteps($statistics);
        $this->printPendingSteps($statistics);
        $this->printSummary($statistics);
        $this->printUndefinedStepsSnippets($statistics);
    }

    public function beforeSuite(Event $event)
    {}

    public function beforeFeature(Event $event)
    {}

    public function afterFeature(Event $event)
    {}

    public function beforeBackground(Event $event)
    {}

    public function afterBackground(Event $event)
    {}

    public function beforeOutline(Event $event)
    {}

    public function beforeOutlineExample(Event $event)
    {}

    public function afterOutlineExample(Event $event)
    {}

    public function afterOutline(Event $event)
    {}

    public function beforeScenario(Event $event)
    {}

    public function afterScenario(Event $event)
    {}

    public function beforeStep(Event $event)
    {}

    public function afterStep(Event $event)
    {
        $this->printStep(
            $event->getSubject(),
            $event->get('result'),
            $event->get('definition'),
            $event->get('snippet'),
            $event->get('exception')
        );
    }

    protected function printStep(StepNode $step, $result, Definition $definition = null,
                                 $snippet = null, \Exception $exception = null)
    {
        switch ($result) {
            case StepTester::PASSED:
                $this->write('{+passed}.{-passed}');
                break;
            case StepTester::SKIPPED:
                $this->write('{+skipped}-{-skipped}');
                break;
            case StepTester::PENDING:
                $this->write('{+pending}P{-pending}');
                break;
            case StepTester::UNDEFINED:
                $this->write('{+undefined}U{-undefined}');
                break;
            case StepTester::FAILED:
                $this->write('{+failed}F{-failed}');
                break;
        }
    }

    protected function printFailedSteps(StatisticsCollector $statistics)
    {
        if (count($statistics->getFailedStepsEvents())) {
            $header = $this->translate('failed steps');
            $this->writeln("{+failed}(::) $header (::){-failed}\n");
            $this->printExceptionEvents($statistics->getFailedStepsEvents());
        }
    }

    protected function printPendingSteps(StatisticsCollector $statistics)
    {
        if (count($statistics->getPendingStepsEvents())) {
            $header = $this->translate('pending steps');
            $this->writeln("{+pending}(::) $header (::){-pending}\n");
            $this->printExceptionEvents($statistics->getPendingStepsEvents());
        }
    }

    protected function printExceptionEvents(array $events)
    {
        foreach ($events as $number => $event) {
            $exception = $event->get('exception');

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

                $this->writeln("{+$color}$error{-$color}");
            }

            $this->printStepPath($event->getSubject(), $event->get('definition'), $exception);
        }
    }

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

    protected function printSummary(StatisticsCollector $statistics)
    {
        $this->printScenariosSummary($statistics);
        $this->printStepsSummary($statistics);

        if ($this->parameters->get('time')) {
            $this->printTimeSummary($statistics);
        }
    }

    protected function printScenariosSummary(StatisticsCollector $statistics)
    {
        $count  = $statistics->getScenariosCount();
        $header = $this->translateChoice(
            '{0} No scenarios|{1} 1 scenario|]1,Inf] %1% scenarios', $count, array('%1%' => $count)
        );
        $this->write($header);
        $this->printStatusesSummary($statistics->getScenariosStatuses());
    }

    protected function printStepsSummary(StatisticsCollector $statistics)
    {
        $count  = $statistics->getStepsCount();
        $header = $this->translateChoice(
            '{0} No steps|{1} 1 step|]1,Inf] %1% steps', $count, array('%1%' => $count)
        );
        $this->write($header);
        $this->printStatusesSummary($statistics->getStepsStatuses());
    }

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

    protected function printTimeSummary(StatisticsCollector $statistics)
    {
        $time       = $statistics->getTotalTime();
        $minutes    = floor($time / 60);
        $seconds    = round($time - ($minutes * 60), 3);

        $this->writeln($minutes . 'm' . $seconds . 's');
    }

    protected function printUndefinedStepsSnippets(StatisticsCollector $statistics)
    {
        if (count($statistics->getDefinitionsSnippets())) {
            $header = $this->translate(
                'You can implement step definitions for undefined steps with these snippets:'
            );
            $this->writeln("\n{+undefined}$header{-undefined}\n");

            foreach ($statistics->getDefinitionsSnippets() as $key => $snippet) {
                $this->writeln("{+undefined}$snippet{-undefined}\n");
            }
        }
    }

    protected function printPathComment($file, $line, $indentCount)
    {
        $indent = str_repeat(' ', $indentCount);
        if (null !== ($basePath = $this->parameters->get('base_path'))) {
            $file = str_replace(dirname($basePath) . '/', '', $file);
        }

        $this->writeln("$indent {+comment}# $file:$line{-comment}");
    }
}

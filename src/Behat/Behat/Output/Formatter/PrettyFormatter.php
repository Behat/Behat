<?php

namespace Behat\Behat\Output\Formatter;

use Behat\Behat\Definition\DefinitionInterface;
use Behat\Behat\Event\BackgroundEvent;
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Event\ExampleEvent;
use Behat\Behat\Event\FeatureEvent;
use Behat\Behat\Event\OutlineEvent;
use Behat\Behat\Event\ScenarioEvent;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\Exception\UndefinedException;
use Behat\Behat\Hook\Event\HookEvent;
use Behat\Behat\Snippet\EventSubscriber\SnippetsCollector;
use Behat\Behat\Tester\EventSubscriber\StatisticsCollector;
use Behat\Gherkin\Node\BackgroundNode;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\KeywordNodeInterface;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\ScenarioLikeInterface;
use Behat\Gherkin\Node\TableNode;

class PrettyFormatter extends CliFormatter
{
    /**
     * @var integer
     */
    private $maxLineLength = 0;
    /**
     * @var StepEvent[]
     */
    private $exampleRowEvents = array();

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::BEFORE_FEATURE    => array('printFeatureHeader', -50),
            EventInterface::BEFORE_SCENARIO   => array('printScenarioHeader', -50),
            EventInterface::AFTER_SCENARIO    => array('printScenarioFooter', -50),
            EventInterface::BEFORE_BACKGROUND => array('printBackgroundHeader', -50),
            EventInterface::AFTER_BACKGROUND  => array('printBackgroundFooter', -50),
            EventInterface::BEFORE_OUTLINE    => array('printOutlineHeader', -50),
            EventInterface::AFTER_OUTLINE     => array('printOutlineFooter', -50),
            EventInterface::AFTER_STEP        => array('printStep', -50),
            EventInterface::AFTER_EXAMPLE     => array('printExampleRow', -50),
            EventInterface::AFTER_HOOK        => array('printHookExceptionOrStdOut', -50),
            EventInterface::AFTER_EXERCISE    => array('printExerciseStats', -50),
        );
    }

    /**
     * Returns formatter name.
     *
     * @return string
     */
    public function getName()
    {
        return 'pretty';
    }

    /**
     * Returns formatter description.
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Prints the feature as is.';
    }

    /**
     * Prints feature header on BEFORE_FEATURE event.
     *
     * @param FeatureEvent $event
     */
    public function printFeatureHeader(FeatureEvent $event)
    {
        $feature = $event->getFeature();
        $keyword = $feature->getKeyword();

        if ($feature->hasTags()) {
            $tags = implode(' ', array_map(function ($tag) {
                return '@' . $tag;
            }, $feature->getTags()));

            $this->writeln("{+tag}$tags{-tag}");
        }

        $this->writeln(trim("$keyword: " . $feature->getTitle()));

        if ($feature->hasDescription()) {
            foreach (explode("\n", $feature->getDescription()) as $line) {
                $this->writeln("  $line");
            }
        }

        if ($feature->hasBackground()) {
            $this->recalculateMaxLineLength($feature->getBackground());
        }

        $this->writeln();
    }

    /**
     * Prints scenario header on BEFORE_SCENARIO event.
     *
     * @param ScenarioEvent $event
     */
    public function printScenarioHeader(ScenarioEvent $event)
    {
        $scenario = $event->getScenario();
        $feature = $scenario->getFeature();

        if ($feature->hasBackground() && 0 == $scenario->getIndex()) {
            return;
        }

        if ($scenario->hasOwnTags()) {
            $tags = implode(' ', array_map(function ($tag) {
                return '@' . $tag;
            }, $scenario->getOwnTags()));

            $this->writeln("  {+tag}$tags{-tag}");
        }

        $this->recalculateMaxLineLength($scenario);
        $this->printKeywordNodeTitle($scenario);
    }

    /**
     * Prints scenario footer on AFTER_SCENARIO event.
     */
    public function printScenarioFooter()
    {
        $this->writeln();
    }

    /**
     * Prints background header on BEFORE_BACKGROUND event.
     *
     * @param BackgroundEvent $event
     */
    public function printBackgroundHeader(BackgroundEvent $event)
    {
        $scenario = $event->getScenario();
        $container = $event->getContainer();
        $background = $event->getBackground();

        if ($container instanceof ExampleNode && 0 !== $container->getIndex()) {
            return;
        }

        if (0 !== $scenario->getIndex()) {
            return;
        }

        $this->printKeywordNodeTitle($background);
    }

    /**
     * Prints background footer on AFTER_BACKGROUND event.
     *
     * @param BackgroundEvent $event
     */
    public function printBackgroundFooter(BackgroundEvent $event)
    {
        $scenario = $event->getScenario();
        $container = $event->getContainer();

        if ($container instanceof ExampleNode && 0 !== $container->getIndex()) {
            return;
        }

        if (0 !== $scenario->getIndex()) {
            return;
        }

        $this->writeln();

        if ($scenario->hasOwnTags()) {
            $tags = implode(' ', array_map(function ($tag) {
                return '@' . $tag;
            }, $scenario->getOwnTags()));

            $this->writeln("  {+tag}$tags{-tag}");
        }

        $this->recalculateMaxLineLength($scenario);
        $this->printKeywordNodeTitle($scenario);
    }

    /**
     * Prints outline header on BEFORE_OUTLINE event.
     *
     * @param OutlineEvent $event
     */
    public function printOutlineHeader(OutlineEvent $event)
    {
        $outline = $event->getOutline();
        $feature = $outline->getFeature();

        if ($feature->hasBackground() && 0 == $outline->getIndex()) {
            return;
        }

        if ($outline->hasOwnTags()) {
            $tags = implode(' ', array_map(function ($tag) {
                return '@' . $tag;
            }, $outline->getOwnTags()));

            $this->writeln("  {+tag}$tags{-tag}");
        }

        $this->recalculateMaxLineLength($outline);
        $this->printKeywordNodeTitle($outline);
    }

    /**
     * Prints outline footer on AFTER_OUTLINE event.
     */
    public function printOutlineFooter()
    {
        $this->writeln();
    }

    /**
     * Prints example row results on AFTER_EXAMPLE event.
     *
     * @param ExampleEvent $event
     */
    public function printExampleRow(ExampleEvent $event)
    {
        $example = $event->getExample();
        $table = $example->getOutline()->getExampleTable();

        if (0 == $example->getIndex()) {
            $keyword = $table->getKeyword();

            $this->writeln();
            $this->writeln("    $keyword:");

            $row = $table->getRowAsStringWithWrappedValues(0, function ($val) {
                return "{+skipped_param}$val{-skipped_param}";
            });

            $this->writeln("      $row");
        }

        if (!$this->getParameter('expand')) {
            $this->printExampleRowResult($event);
        } else {
            $this->writeln();
            $this->printExpandedExampleResult($event);
        }

        $this->exampleRowEvents = array();
    }

    /**
     * Generates example row column.
     *
     * @param string  $value
     * @param integer $column
     *
     * @return string
     */
    public function getExampleRowCell($value, $column)
    {
        $status = StepEvent::PASSED;

        foreach ($this->exampleRowEvents as $event) {
            $header = $event->getStep()->getContainer()->getOutline()->getExampleTable()->getRow(0);
            $steps = $event->getStep()->getContainer()->getOutline()->getSteps();
            $outlineStepText = $steps[$event->getStep()->getIndex()]->getText();

            if (false !== strpos($outlineStepText, '<' . $header[$column] . '>')) {
                $status = max($status, $event->getStatus());
            }
        }

        $color = $this->getColorCode($status);

        return "{+$color}$value{-$color}";
    }

    /**
     * Prints step on AFTER_STEP event.
     *
     * @param StepEvent $event
     */
    public function printStep(StepEvent $event)
    {
        $step = $event->getStep();

        // It is a background step
        if ($step->getContainer() instanceof BackgroundNode) {
            $this->printBackgroundStep($event);

            return;
        }

        // It is an example step
        if ($step->getContainer() instanceof ExampleNode) {
            $this->printExampleStep($event);

            return;
        }

        $this->printStepBody($event);
    }

    /**
     * Prints exercise stats on AFTER_EXERCISE event.
     */
    public function printExerciseStats()
    {
        $this->printSummary($this->getStatisticsCollector());
        $this->printUndefinedStepsSnippets($this->getSnippetsCollector());
    }


    /**
     * Prints hook stdOut or exception.
     *
     * @param HookEvent $event
     */
    public function printHookExceptionOrStdOut(HookEvent $event)
    {
        if (!$event->hasStdOut() && !$event->hasException()) {
            return;
        }

        $lcEvent = $event->getLifecycleEvent();
        $hook = $event->getHook();
        $indent = '';

        if ($lcEvent instanceof ScenarioEvent) {
            $indent = '  ';
        }
        if ($lcEvent instanceof ExampleEvent) {
            $indent = '    ';
        }
        if ($lcEvent instanceof StepEvent) {
            $indent = '    ';
        }

        $hookText = $hook->toString();
        if ($event->hasException()) {
            $this->write("$indent{+failed}> $hookText{-failed}");
        } else {
            $this->write("$indent{+passed}> $hookText{-passed}");
        }

        if ($this->getParameter('paths')) {
            $path = $this->relativizePathsInString($hook->getPath());
            $this->printLineComment($path, mb_strlen("$indent> $hookText", 'utf8'));
        }

        $this->writeln();

        if ($event->hasStdOut()) {
            $this->writeln("$indent  " . strtr($event->getStdOut(), array("\n" => "\n$indent  ")));
        }
        if ($event->hasException()) {
            $error = $this->exceptionToString($event->getException());
            $error = $this->relativizePathsInString($error);
            $this->writeln(
                "$indent  {+failed}" . strtr($error, array("\n" => "\n$indent  ")) . "{-failed}"
            );
        }
    }

    protected function printKeywordNodeTitle(KeywordNodeInterface $node)
    {
        $keyword = $node->getKeyword();

        $lines = explode("\n", $node->getTitle());
        $title = array_shift($lines);
        $this->write($firstLine = "  $keyword:" . ($title ? " $title" : ''));

        if ($this->getParameter('paths')) {
            $path = $this->relativizePathsInString($node->getFile()) . ':' . $node->getLine();
            $this->printLineComment($path, mb_strlen($firstLine, 'utf8'));
        }

        $this->writeln();

        foreach ($lines as $line) {
            $this->writeln("    $line");
        }
    }

    protected function printBackgroundStep(StepEvent $event)
    {
        // Skip non-failing background steps in scenarios
        if (0 !== $event->getScenario()->getIndex() && $event->getStatus() < StepEvent::FAILED) {
            return;
        }

        if ($event->getContainer() instanceof ExampleNode && 0 !== $event->getContainer()->getIndex()) {
            return;
        }

        $this->printStepBody($event);
    }

    protected function printExampleStep(StepEvent $event)
    {
        if (0 == $event->getStep()->getContainer()->getIndex()) {
            $this->printStepBody($event, true);
        }

        $this->exampleRowEvents[] = $event;
    }

    protected function printStepBody(StepEvent $event, $outlineStep = false, $indent = '    ')
    {
        $step = $event->getStep();

        // If it is example step - use outline step instead
        if ($outlineStep) {
            $steps = $step->getContainer()->getOutline()->getSteps();
            $step = $steps[$step->getIndex()];
        }

        $type = $step->getType();
        $text = $step->getText();
        $color = $this->getColorCode($outlineStep ? StepEvent::SKIPPED : $event->getStatus());

        // Print step text
        if ($event->hasDefinition()) {
            $colored = $this->colorizeDefinitionArguments($text, $event->getDefinition(), $color);
            $this->write("$indent{+$color}$type $colored{-$color}");
        } else {
            $this->write("$indent{+$color}$type $text{-$color}");
        }

        // Print definition path (if some & enabled)
        if ($this->getParameter('paths') && $event->hasDefinition()) {
            $path = $this->relativizePathsInString($event->getDefinition()->getPath());
            $this->printLineComment($path, mb_strlen("$indent$type $text", 'utf8'));
        }

        $this->writeln();

        // Print multiline arguments
        if ($this->getParameter('multiline_arguments') && $step->hasArguments()) {
            foreach ($step->getArguments() as $argument) {
                if ($argument instanceof PyStringNode) {
                    $this->printStepPyStringArgument($argument, $color, $indent . '  ');
                } elseif ($argument instanceof TableNode) {
                    $this->printStepTableArgument($argument, $color, $indent . '  ');
                }
            }
        }

        if ($outlineStep) {
            return;
        }

        // Print step StdOut
        if ($event->hasStdOut()) {
            $this->writeln("$indent  " . strtr($event->getStdOut(), array("\n" => "\n$indent  ")));
        }

        // Print step exception
        if ($event->hasException() && !($event->getException() instanceof UndefinedException)) {
            $error = $this->exceptionToString($event->getException());
            $error = $this->relativizePathsInString($error);
            $this->writeln("$indent  {+$color}" . strtr($error, array("\n" => "\n$indent  ")) . "{-$color}");
        }
    }

    protected function printExampleRowResult(ExampleEvent $event)
    {
        $example = $event->getExample();
        $table = $example->getOutline()->getExampleTable();

        // Example result row
        $row = $table->getRowAsStringWithWrappedValues($example->getIndex() + 1,
            array($this, 'getExampleRowCell')
        );
        $this->writeln("      $row");

        foreach ($this->exampleRowEvents as $event) {
            // Print step StdOut
            if ($event->hasStdOut()) {
                $this->writeln("        " . strtr($event->getStdOut(), array("\n" => "\n        ")));
            }

            // Print step exception
            if ($event->hasException() && !($event->getException() instanceof UndefinedException)) {
                $color = $this->getColorCode($event->getStatus());
                $error = $this->exceptionToString($event->getException());
                $error = $this->relativizePathsInString($error);
                $this->writeln("        {+$color}" . strtr($error, array("\n" => "\n        ")) . "{-$color}");
            }
        }
    }

    protected function printExpandedExampleResult(ExampleEvent $event)
    {
        $example = $event->getExample();
        $this->write($firstLine = "      " . $example->getTitle());

        if ($this->getParameter('paths')) {
            $path = $this->relativizePathsInString($example->getFile()) . ':' . $example->getLine();
            $this->printLineComment($path, mb_strlen($firstLine, 'utf8'));
        }

        $this->writeln();

        foreach ($this->exampleRowEvents as $event) {
            $this->printStepBody($event, false, '        ');
        }
    }

    protected function printStepPyStringArgument(PyStringNode $pystring, $color, $indent)
    {
        $string = strtr(
            sprintf("$indent\"\"\"\n%s\n\"\"\"", (string)$pystring), array("\n" => "\n$indent")
        );

        if (null !== $color) {
            $this->writeln("{+$color}$string{-$color}");
        } else {
            $this->writeln($string);
        }
    }

    protected function printStepTableArgument(TableNode $table, $color, $indent)
    {
        $string = strtr($indent . (string)$table, array("\n" => "\n$indent"));

        if (null !== $color) {
            $this->writeln("{+$color}$string{-$color}");
        } else {
            $this->writeln($string);
        }
    }

    protected function printSummary(StatisticsCollector $stats)
    {
        if (count($stats->getFailedStepsEvents())) {
            $this->maxLineLength = 0;
            $header = $this->translate('failing_scenarios_title');
            $this->writeln("{+failed}$header:{-failed}");
            foreach ($stats->getFailedStepsEvents() as $event) {
                $scenario = $event->getScenario();

                $file = $this->relativizePathsInString($scenario->getFile());
                $line = $event->getContainer()->getLine();
                $text = "$file:$line";
                $this->write("{+failed}$text{-failed}");

                if ($this->getParameter('paths')) {
                    $this->maxLineLength = max($this->maxLineLength, mb_strlen($text, 'utf8'));
                    $this->printLineComment(
                        $scenario->getKeyword() . ': ' . $scenario->getTitle(),
                        mb_strlen($text, 'utf8')
                    );
                }

                $this->writeln();
            }

            $this->writeln();
        }

        $count = $stats->getScenariosCount();
        $header = $this->translateChoice('scenarios_count', $count, array('%1%' => $count));
        $this->write($header);
        $this->printStatusesSummary($stats->getScenariosStatuses());

        $count = $stats->getStepsCount();
        $header = $this->translateChoice('steps_count', $count, array('%1%' => $count));
        $this->write($header);
        $this->printStatusesSummary($stats->getStepsStatuses());

        if ($this->getParameter('time')) {
            $this->printTimeSummary($stats);
        }
    }

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

    protected function printTimeSummary(StatisticsCollector $stats)
    {
        $time = $stats->getTotalTime();
        $minutes = floor($time / 60);
        $seconds = round($time - ($minutes * 60), 3);

        $this->writeln($minutes . 'm' . $seconds . 's');
    }

    protected function printUndefinedStepsSnippets(SnippetsCollector $snippets)
    {
        if (!$this->getParameter('snippets') || !$snippets->hasSnippets()) {
            return;
        }

        $header = $this->translate('proposal_title');
        $this->writeln("\n{+undefined}$header{-undefined}\n");

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

    protected function printLineComment($path, $lineLength = 0)
    {
        $indent = str_repeat(' ', max(0, $this->maxLineLength - $lineLength));

        $this->write("$indent {+comment}# $path{-comment}");
    }

    protected function recalculateMaxLineLength(ScenarioLikeInterface $scenario)
    {
        $keyword = $scenario->getKeyword();
        $lines = explode("\n", $scenario->getTitle());
        $title = array_shift($lines);
        $scenarioTitle = "  $keyword:" . ($title ? " $title" : '');

        $this->maxLineLength = max($this->maxLineLength, mb_strlen($scenarioTitle, 'utf8'));

        foreach ($scenario->getSteps() as $step) {
            $type = $step->getType();
            $text = $step->getText();
            $indent = '    ';

            $container = $step->getContainer();
            if ($container instanceof ExampleNode) {
                $steps = $container->getOutline()->getSteps();
                $text = $steps[$step->getIndex()];
            }

            $stepDescription = "$indent$type $text";
            $this->maxLineLength = max($this->maxLineLength, mb_strlen($stepDescription, 'utf8'));
        }
    }

    protected function relativizePathsInString($string)
    {
        if ($basePath = getcwd()) {
            $basePath = realpath($basePath) . DIRECTORY_SEPARATOR;
            $string = str_replace($basePath, '', $string);
        }

        return $string;
    }

    protected function colorizeDefinitionArguments($text, DefinitionInterface $definition, $color)
    {
        $regex = $definition->getRegex();
        $paramColor = $color . '_param';

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
            $format = "{-$color}{+$paramColor}%s{-$paramColor}{+$color}";
            $text = sprintf("%s{$format}%s", $begin, $value, $end);

            // Keep track of how many extra characters are added
            $shift += strlen($format) - 2;
            $lastReplacementPosition += strlen($format) - 2;
        }

        // Replace "<", ">" with colorized ones
        $text = preg_replace('/(<[^>]+>)/',
            "{-$color}{+$paramColor}\$1{-$paramColor}{+$color}",
            $text
        );

        return $text;
    }

    /**
     * Returns default parameters to construct ParameterBag.
     *
     * @return array
     */
    protected function getDefaultParameters()
    {
        return array(
            'expand'              => false,
            'multiline_arguments' => true,
        );
    }
}

<?php

namespace Behat\Behat\Formatter;

use Symfony\Component\EventDispatcher\Event;

use Behat\Behat\Tester\StepTester,
    Behat\Behat\StepDefinition\Definition,
    Behat\Behat\DataCollector\LoggerDataCollector;

use Behat\Gherkin\Node\AbstractNode,
    Behat\Gherkin\Node\FeatureNode,
    Behat\Gherkin\Node\BackgroundNode,
    Behat\Gherkin\Node\AbstractScenarioNode,
    Behat\Gherkin\Node\OutlineNode,
    Behat\Gherkin\Node\ScenarioNode,
    Behat\Gherkin\Node\StepNode,
    Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

class PrettyFormatter extends ProgressFormatter
{
    protected   $maxLineLength          = 0;

    protected   $inBackground           = false;
    protected   $isBackgroundPrinted    = false;
    protected   $inOutlineSteps         = false;
    protected   $inOutlineExample       = false;
    protected   $isOutlineHeaderPrinted = false;

    protected   $delayedScenarioEvent;
    protected   $delayedStepEvents      = array();

    protected function getDefaultParameters()
    {
        return array();
    }

    public function afterSuite(Event $event)
    {
        $logger = $event->getSubject();

        $this->printSummary($logger);
        $this->printUndefinedStepsSnippets($logger);
    }

    public function beforeFeature(Event $event)
    {
        $feature = $event->getSubject();

        $this->isBackgroundPrinted = false;

        $this->printFeatureOrScenarioTags($feature);
        $this->printFeatureName($feature);
        if (null !== $feature->getDescription()) {
            $this->printFeatureDescription($feature);
        }
        $this->writeln();
    }

    public function beforeBackground(Event $event)
    {
        $this->inBackground = true;

        if ($this->isBackgroundPrinted) {
            return;
        }

        $background = $event->getSubject();

        $this->maxLineLength = $this->getMaxLineLength($this->maxLineLength, $background);

        $this->printScenarioName($background);
        $this->printScenarioPath($background);
    }

    public function afterBackground(Event $event)
    {
        $this->inBackground = false;

        if ($this->isBackgroundPrinted) {
            return;
        }
        $this->isBackgroundPrinted = true;

        $this->writeln();

        if (null !== $this->delayedScenarioEvent) {
            $method = $this->delayedScenarioEvent[0];
            $event  = $this->delayedScenarioEvent[1];

            $this->$method($event);
        }
    }

    public function beforeOutline(Event $event)
    {
        $outline = $event->getSubject();

        if (!$this->isBackgroundPrinted && $outline->getFeature()->hasBackground()) {
            $this->delayedScenarioEvent = array(__FUNCTION__, $event);

            return;
        }

        $this->isOutlineHeaderPrinted   = false;
        $this->maxLineLength            = $this->getMaxLineLength($this->maxLineLength, $outline);

        $this->printFeatureOrScenarioTags($outline);
        $this->printScenarioName($outline);
        $this->printScenarioPath($outline);
    }

    public function beforeOutlineExample(Event $event)
    {
        $this->inOutlineExample     = true;
        $this->delayedStepEvents    = array();
    }

    public function afterOutlineExample(Event $event)
    {
        $this->inOutlineExample = false;

        $outline = $event->getSubject();

        if (!$this->isOutlineHeaderPrinted) {
            $this->printOutlineSteps($outline);
            $this->printOutlineExamplesHeader($outline->getExamples());
            $this->isOutlineHeaderPrinted = true;
        }

        $this->printOutlineExampleResult(
            $outline->getExamples(),
            $event->get('iteration'),
            $event->get('result'),
            $event->get('skipped')
        );
    }

    public function afterOutline(Event $event)
    {
        $this->writeln();
    }

    public function beforeScenario(Event $event)
    {
        $scenario = $event->getSubject();

        if (!$this->isBackgroundPrinted && $scenario->getFeature()->hasBackground()) {
            $this->delayedScenarioEvent = array(__FUNCTION__, $event);

            return;
        }

        $this->maxLineLength = $this->getMaxLineLength($this->maxLineLength, $scenario);

        $this->printFeatureOrScenarioTags($scenario);
        $this->printScenarioName($scenario);
        $this->printScenarioPath($scenario);
    }

    public function afterScenario(Event $event)
    {
        $this->writeln();
    }

    public function afterStep(Event $event)
    {
        if ($this->inBackground && $this->isBackgroundPrinted) {
            return;
        }

        if (!$this->inBackground && $this->inOutlineExample) {
            $this->delayedStepEvents[] = $event;

            return;
        }

        $step = $event->getSubject();

        $this->printStep(
            $step,
            $event->get('result'),
            $event->get('definition'),
            $event->get('snippet'),
            $event->get('exception')
        );
    }

    protected function printFeatureOrScenarioTags(AbstractNode $node)
    {
        if (count($tags = $node->getTags())) {
            $tags = implode(' ', array_map(function($tag){
                return '@' . $tag;
            }, $tags));

            if ($node instanceof FeatureNode) {
                $indent = '';
            } else {
                $indent = '  ';
            }

            $this->writeln("$indent{+tags}$tags{-tags}");
        }
    }

    protected function printFeatureName(FeatureNode $feature)
    {
        $this->writeln($this->getFeatureOrScenarioName($feature));
    }

    protected function printFeatureDescription(FeatureNode $feature)
    {
        $lines = explode("\n", $feature->getDescription());

        foreach ($lines as $line) {
            $this->writeln("  $line");
        }
    }

    protected function printScenarioName(AbstractScenarioNode $scenario)
    {
        $this->write($this->getFeatureOrScenarioName($scenario));
    }

    protected function printScenarioPath(AbstractScenarioNode $scenario)
    {
        $nameLength     = mb_strlen($this->getFeatureOrScenarioName($scenario));
        $indentCount    = $nameLength > $this->maxLineLength ? 0 : $this->maxLineLength - $nameLength;

        $this->printPathComment($scenario->getFile(), $scenario->getLine(), $indentCount);
    }

    protected function printOutlineSteps(OutlineNode $outline)
    {
        $this->inOutlineSteps = true;

        foreach ($this->delayedStepEvents as $event) {
            $step       = $event->getSubject();
            $definition = $event->get('definition');

            $this->printStep($step, StepTester::SKIPPED, $definition);
        }

        $this->inOutlineSteps = false;

        $this->writeln();
    }

    protected function printOutlineExamplesHeader(TableNode $examples)
    {
        $keyword = $examples->getKeyword();
        $this->writeln("    $keyword:");
        $header  = preg_replace(
            '/|([^|]*)|/',
            '{+skipped}$1{-skipped}',
            '      ' . $examples->getRowAsString(0)
        );

        $this->writeln($header);
    }

    protected function printOutlineExampleResult(TableNode $examples, $iteration, $result, $isSkipped)
    {
        $color  = $this->getResultColorCode($result);
        $row    = preg_replace(
            '/|([^|]*)|/',
            "{+$color}\$1{-$color}",
            '      ' . $examples->getRowAsString($iteration + 1)
        );

        $this->writeln($row);
        $this->printOutlineExampleResultExceptions($this->delayedStepEvents);
    }

    protected function printOutlineExampleResultExceptions(array $events)
    {
        foreach ($events as $event) {
            if (null !== ($exception = $event->get('exception'))) {
                $color = $this->getResultColorCode($event->get('result'));

                if ($this->parameters->get('verbose')) {
                    $error = (string) $exception;
                } else {
                    $error = $exception->getMessage();
                }
                $error = $this->relativizePathsInString($error);

                $this->writeln(
                    "        {+$color}" . strtr($error, array("\n" => "\n      ")) . "{-$color}"
                );
            }
        }
    }

    protected function printStep(StepNode $step, $result, Definition $definition = null,
                                 $snippet = null, \Exception $exception = null)
    {
        $type   = $step->getType();
        $text   = $this->inOutlineSteps ? $step->getCleanText() : $step->getText();
        $color  = $this->getResultColorCode($result);

        $this->write("    {+$color}$type $text{-$color}");

        if (null !== $definition) {
            $this->printStepDefinitionPath($step, $definition);
        } else {
            $this->writeln();
        }

        $this->printStepArguments($step->getArguments(), $color);

        if (null !== $exception) {
            $this->printStepException($exception, $color);
        }
    }

    protected function printStepDefinitionPath(StepNode $step, Definition $definition)
    {
        $type           = $step->getType();
        $text           = $this->inOutlineSteps ? $step->getCleanText() : $step->getText();
        $nameLength     = mb_strlen("    $type $text");
        $indentCount    = $nameLength > $this->maxLineLength ? 0 : $this->maxLineLength - $nameLength;

        $this->printPathComment($definition->getFile(), $definition->getLine(), $indentCount);
    }

    protected function printStepArguments(array $arguments, $color)
    {
        foreach ($arguments as $argument) {
            if ($argument instanceof PyStringNode) {
                $this->printPyString($argument, $color);
            } elseif ($argument instanceof TableNode) {
                $this->printTable($argument, $color);
            }
        }
    }

    protected function printStepException(\Exception $exception, $color)
    {
        if ($this->parameters->get('verbose')) {
            $error = (string) $exception;
        } else {
            $error = $exception->getMessage();
        }
        $error = $this->relativizePathsInString($error);

        $this->writeln(
            "      {+$color}" . strtr($error, array("\n" => "\n      ")) . "{-$color}"
        );
    }

    protected function printPyString(PyStringNode $pystring, $color = null)
    {
        $string = strtr(
            sprintf("      \"\"\"\n%s\n\"\"\"", (string) $pystring), array("\n" => "\n      ")
        );

        if (null !== $color) {
            $this->writeln("{+$color}$string{-$color}");
        } else {
            $this->writeln($string);
        }
    }

    protected function printTable(TableNode $table, $color = null)
    {
        $string = strtr('      ' . (string) $table, array("\n" => "\n      "));

        if (null !== $color) {
            $this->writeln("{+$color}$string{-$color}");
        } else {
            $this->writeln($string);
        }
    }

    protected function getFeatureOrScenarioName(AbstractNode $node, $haveBaseIndent = true)
    {
        $keyword    = $node->getKeyword();
        $baseIndent = ($node instanceof FeatureNode) || !$haveBaseIndent ? '' : '  ';

        if (!$node instanceof BackgroundNode) {
            $lines = explode("\n", $node->getTitle());
            $title = array_shift($lines);

            if (count($lines)) {
                $indent = $baseIndent . str_repeat(' ', mb_strlen("$keyword: "));
                foreach ($lines as $line) {
                    $title .= "\n" . $indent . $line;
                }
            }

            return "$baseIndent$keyword:" . ($title ? ' ' . $title : '');
        }

        return "$baseIndent$keyword:";
    }

    /**
     * Recalculate max descriptions size for section elements.
     *
     * @param   BackgroundNode $scenario   element for calculations
     * 
     * @return  integer                 description length
     */
    protected function getMaxLineLength($max, AbstractScenarioNode $scenario)
    {
        $lines = explode("\n", $this->getFeatureOrScenarioName($scenario, false));

        foreach ($lines as $line) {
            $max = max($max, mb_strlen($line) + 2);
        }

        foreach ($scenario->getSteps() as $step) {
            $stepDescription = $step->getType() . ' ' . $step->getCleanText();
            $max = max($max, mb_strlen($stepDescription) + 4);
        }

        return $max;
    }
}

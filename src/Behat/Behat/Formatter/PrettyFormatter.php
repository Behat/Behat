<?php

namespace Behat\Behat\Formatter;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Behat\Behat\Definition\Definition,
    Behat\Behat\DataCollector\LoggerDataCollector,
    Behat\Behat\Event\SuiteEvent,
    Behat\Behat\Event\FeatureEvent,
    Behat\Behat\Event\ScenarioEvent,
    Behat\Behat\Event\BackgroundEvent,
    Behat\Behat\Event\OutlineEvent,
    Behat\Behat\Event\OutlineExampleEvent,
    Behat\Behat\Event\StepEvent;

use Behat\Gherkin\Node\AbstractNode,
    Behat\Gherkin\Node\FeatureNode,
    Behat\Gherkin\Node\BackgroundNode,
    Behat\Gherkin\Node\AbstractScenarioNode,
    Behat\Gherkin\Node\OutlineNode,
    Behat\Gherkin\Node\ScenarioNode,
    Behat\Gherkin\Node\StepNode,
    Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Pretty formatter.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PrettyFormatter extends ProgressFormatter
{
    /**
     * Maximum line length.
     *
     * @var     integer
     */
    protected   $maxLineLength          = 0;
    /**
     * Are we in background.
     *
     * @var     boolean
     */
    protected   $inBackground           = false;
    /**
     * Is background printed.
     *
     * @var     boolean
     */
    protected   $isBackgroundPrinted    = false;
    /**
     * Are we in outline steps.
     *
     * @var     boolean
     */
    protected   $inOutlineSteps         = false;
    /**
     * Are we in outline example.
     *
     * @var     boolean
     */
    protected   $inOutlineExample       = false;
    /**
     * Is outline headline printed.
     *
     * @var     boolean
     */
    protected   $isOutlineHeaderPrinted = false;
    /**
     * Delayed scenario event.
     *
     * @var     Symfony\Component\EventDispatcher\Event
     */
    protected   $delayedScenarioEvent;
    /**
     * Delayed step events.
     *
     * @var     array
     */
    protected   $delayedStepEvents      = array();

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
        return array(
            'beforeSuite' => 'beforeSuite',
            'afterSuite' => 'afterSuite',
            'beforeFeature' => 'beforeFeature',
            'afterFeature' => 'afterFeature',
            'beforeScenario' => 'beforeScenario',
            'afterScenario' => 'afterScenario',
            'beforeBackground' => 'beforeBackground',
            'afterBackground' => 'afterBackground',
            'beforeOutline' => 'beforeOutline',
            'afterOutline' => 'afterOutline',
            'beforeOutlineExample' => 'beforeOutlineExample',
            'afterOutlineExample' => 'afterOutlineExample',
            'afterStep' => 'afterStep'
        );
    }

    /**
     * Listens to "suite.before" event.
     *
     * @param   Behat\Behat\Event\SuiteEvent    $event
     *
     * @uses    printSuiteHeader()
     */
    public function beforeSuite(SuiteEvent $event)
    {
        $this->printSuiteHeader($event->getLogger());
    }

    /**
     * Listens to "suite.after" event.
     *
     * @param   Behat\Behat\Event\SuiteEvent    $event
     *
     * @uses    printSuiteFooter()
     */
    public function afterSuite(SuiteEvent $event)
    {
        $this->printSuiteFooter($event->getLogger());
    }

    /**
     * Listens to "feature.before" event.
     *
     * @param   Behat\Behat\Event\FeatureEvent  $event
     *
     * @uses    printFeatureHeader()
     */
    public function beforeFeature(FeatureEvent $event)
    {
        $this->isBackgroundPrinted = false;
        $this->printFeatureHeader($event->getFeature());
    }

    /**
     * Listens to "feature.after" event.
     *
     * @param   Behat\Behat\Event\FeatureEvent  $event
     *
     * @uses    printFeatureFooter()
     */
    public function afterFeature(FeatureEvent $event)
    {
        $this->printFeatureFooter($event->getFeature());
    }

    /**
     * Listens to "background.before" event.
     *
     * @param   Behat\Behat\Event\BackgroundEvent  $event
     *
     * @uses    printBackgroundHeader()
     */
    public function beforeBackground(BackgroundEvent $event)
    {
        $this->inBackground = true;

        if ($this->isBackgroundPrinted) {
            return;
        }

        $this->printBackgroundHeader($event->getBackground());
    }

    /**
     * Listens to "background.after" event.
     *
     * @param   Behat\Behat\Event\BackgroundEvent  $event
     *
     * @uses    printBackgroundFooter()
     */
    public function afterBackground(BackgroundEvent $event)
    {
        $this->inBackground = false;

        if ($this->isBackgroundPrinted) {
            return;
        }
        $this->isBackgroundPrinted = true;

        $this->printBackgroundFooter($event->getBackground());

        if (null !== $this->delayedScenarioEvent) {
            $method = $this->delayedScenarioEvent[0];
            $event  = $this->delayedScenarioEvent[1];

            $this->$method($event);
        }
    }

    /**
     * Listens to "outline.before" event.
     *
     * @param   Behat\Behat\Event\OutlineEvent  $event
     *
     * @uses    printOutlineHeader()
     */
    public function beforeOutline(OutlineEvent $event)
    {
        $outline = $event->getOutline();

        if (!$this->isBackgroundPrinted && $outline->getFeature()->hasBackground()) {
            $this->delayedScenarioEvent = array(__FUNCTION__, $event);

            return;
        }

        $this->isOutlineHeaderPrinted   = false;

        $this->printOutlineHeader($outline);
    }

    /**
     * Listens to "outline.example.before" event.
     *
     * @param   Behat\Behat\Event\OutlineExampleEvent  $event
     *
     * @uses    printOutlineExampleHeader()
     */
    public function beforeOutlineExample(OutlineExampleEvent $event)
    {
        $this->inOutlineExample     = true;
        $this->delayedStepEvents    = array();

        $this->printOutlineExampleHeader($event->getOutline(), $event->getIteration());
    }

    /**
     * Listens to "outline.example.after" event.
     *
     * @param   Behat\Behat\Event\OutlineExampleEvent  $event
     *
     * @uses    printOutlineExampleFooter()
     */
    public function afterOutlineExample(OutlineExampleEvent $event)
    {
        $this->inOutlineExample = false;

        $this->printOutlineExampleFooter(
            $event->getOutline(), $event->getIteration(), $event->getResult(), $event->isSkipped()
        );
    }

    /**
     * Listens to "outline.after" event.
     *
     * @param   Behat\Behat\Event\OutlineEvent  $event
     *
     * @uses    printOutlineFooter()
     */
    public function afterOutline(OutlineEvent $event)
    {
        $this->printOutlineFooter($event->getOutline());
    }

    /**
     * Listens to "scenario.before" event.
     *
     * @param   Behat\Behat\Event\ScenarioEvent $event
     *
     * @uses    printScenarioHeader()
     */
    public function beforeScenario(ScenarioEvent $event)
    {
        $scenario = $event->getScenario();

        if (!$this->isBackgroundPrinted && $scenario->getFeature()->hasBackground()) {
            $this->delayedScenarioEvent = array(__FUNCTION__, $event);

            return;
        }

        $this->printScenarioHeader($scenario);
    }

    /**
     * Listens to "scenario.after" event.
     *
     * @param   Behat\Behat\Event\ScenarioEvent $event
     *
     * @uses    printScenarioFooter()
     */
    public function afterScenario(ScenarioEvent $event)
    {
        $this->printScenarioFooter($event->getScenario());
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
        if ($this->inBackground && $this->isBackgroundPrinted) {
            return;
        }

        if (!$this->inBackground && $this->inOutlineExample) {
            $this->delayedStepEvents[] = $event;

            return;
        }

        $this->printStep(
            $event->getStep(),
            $event->getResult(),
            $event->getDefinition(),
            $event->getSnippet(),
            $event->getException()
        );
    }

    /**
     * Prints feature header.
     *
     * @param   Behat\Gherkin\Node\FeatureNode  $feature
     *
     * @uses    printFeatureOrScenarioTags()
     * @uses    printFeatureName()
     * @uses    printFeatureDescription()
     */
    protected function printFeatureHeader(FeatureNode $feature)
    {
        $this->printFeatureOrScenarioTags($feature);
        $this->printFeatureName($feature);
        if (null !== $feature->getDescription()) {
            $this->printFeatureDescription($feature);
        }

        $this->writeln();
    }

    /**
     * Prints node tags.
     *
     * @param   Behat\Gherkin\Node\AbstractNode     $node
     */
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

            $this->writeln("$indent{+tag}$tags{-tag}");
        }
    }

    /**
     * Prints feature keyword and name.
     *
     * @param   Behat\Gherkin\Node\FeatureNode  $feature
     *
     * @uses    getFeatureOrScenarioName()
     */
    protected function printFeatureName(FeatureNode $feature)
    {
        $this->writeln($this->getFeatureOrScenarioName($feature));
    }

    /**
     * Prints feature description.
     *
     * @param   Behat\Gherkin\Node\FeatureNode  $feature
     */
    protected function printFeatureDescription(FeatureNode $feature)
    {
        $lines = explode("\n", $feature->getDescription());

        foreach ($lines as $line) {
            $this->writeln("  $line");
        }
    }

    /**
     * Prints feature footer.
     *
     * @param   Behat\Gherkin\Node\FeatureNode  $feature
     */
    protected function printFeatureFooter(FeatureNode $feature)
    {
    }

    /**
     * Prints scenario keyword and name.
     *
     * @param   Behat\Gherkin\Node\AbstractScenarioNode $scenario
     *
     * @uses    getFeatureOrScenarioName()
     */
    protected function printScenarioName(AbstractScenarioNode $scenario)
    {
        $this->write($this->getFeatureOrScenarioName($scenario));
    }

    /**
     * Prints scenario definition path.
     *
     * @param   Behat\Gherkin\Node\AbstractScenarioNode $scenario
     *
     * @uses    getFeatureOrScenarioName()
     * @uses    printPathComment()
     */
    protected function printScenarioPath(AbstractScenarioNode $scenario)
    {
        $nameLength     = mb_strlen($this->getFeatureOrScenarioName($scenario));
        $indentCount    = $nameLength > $this->maxLineLength ? 0 : $this->maxLineLength - $nameLength;

        $this->printPathComment($scenario->getFile(), $scenario->getLine(), $indentCount);
    }

    /**
     * Prints background header.
     *
     * @param   Behat\Gherkin\Node\BackgroundNode   $background
     *
     * @uses    printScenarioName()
     * @uses    printScenarioPath()
     */
    protected function printBackgroundHeader(BackgroundNode $background)
    {
        $this->maxLineLength = $this->getMaxLineLength($this->maxLineLength, $background);

        $this->printScenarioName($background);
        $this->printScenarioPath($background);
    }

    /**
     * Prints background footer.
     *
     * @param   Behat\Gherkin\Node\BackgroundNode   $background
     */
    protected function printBackgroundFooter(BackgroundNode $background)
    {
        $this->writeln();
    }

    /**
     * Prints outline header.
     *
     * @param   Behat\Gherkin\Node\OutlineNode  $outline
     *
     * @uses    printFeatureOrScenarioTags()
     * @uses    printScenarioName()
     * @uses    printScenarioPath()
     */
    protected function printOutlineHeader(OutlineNode $outline)
    {
        $this->maxLineLength = $this->getMaxLineLength($this->maxLineLength, $outline);

        $this->printFeatureOrScenarioTags($outline);
        $this->printScenarioName($outline);
        $this->printScenarioPath($outline);
    }

    /**
     * Prints outline footer.
     *
     * @param   Behat\Gherkin\Node\OutlineNode  $outline
     */
    protected function printOutlineFooter(OutlineNode $outline)
    {
        $this->writeln();
    }

    /**
     * Prints outline example header.
     *
     * @param   Behat\Gherkin\Node\OutlineNode  $outline
     * @param   integer                         $iteration
     */
    protected function printOutlineExampleHeader(OutlineNode $outline, $iteration)
    {
    }

    /**
     * Prints outline example result.
     *
     * @param   Behat\Gherkin\Node\OutlineNode  $outline
     * @param   integer                         $iteration  example row
     * @param   integer                         $result     result code
     * @param   boolean                         $isSkipped  is outline example skipped
     *
     * @uses    printOutlineSteps()
     * @uses    printOutlineExamplesSectionHeader()
     * @uses    printOutlineExampleResult()
     */
    protected function printOutlineExampleFooter(OutlineNode $outline, $iteration, $result, $skipped)
    {
        if (!$this->isOutlineHeaderPrinted) {
            $this->printOutlineSteps($outline);
            $this->printOutlineExamplesSectionHeader($outline->getExamples());
            $this->isOutlineHeaderPrinted = true;
        }

        $this->printOutlineExampleResult($outline->getExamples(), $iteration, $result, $skipped);
    }

    /**
     * Prints outline steps.
     *
     * @param   Behat\Gherkin\Node\OutlineNode  $outline
     */
    protected function printOutlineSteps(OutlineNode $outline)
    {
        $this->inOutlineSteps = true;

        foreach ($this->delayedStepEvents as $event) {
            $this->printStep($event->getStep(), StepEvent::SKIPPED, $event->getDefinition());
        }

        $this->inOutlineSteps = false;
    }

    /**
     * Prints outline examples header.
     *
     * @param   Behat\Gherkin\Node\TableNode    $examples
     *
     * @uses    printColorizedTableRow()
     */
    protected function printOutlineExamplesSectionHeader(TableNode $examples)
    {
        $this->writeln();
        $keyword = $examples->getKeyword();
        $this->writeln("    $keyword:");

        $this->printColorizedTableRow($examples->getRowAsString(0), 'skipped');
    }

    /**
     * Prints outline example result.
     *
     * @param   Behat\Gherkin\Node\TableNode    $examples   examples table
     * @param   integer                         $iteration  example row
     * @param   integer                         $result     result code
     * @param   boolean                         $isSkipped  is outline example skipped
     *
     * @uses    printColorizedTableRow()
     * @uses    printOutlineExampleResultExceptions()
     */
    protected function printOutlineExampleResult(TableNode $examples, $iteration, $result, $isSkipped)
    {
        $color  = $this->getResultColorCode($result);

        $this->printColorizedTableRow($examples->getRowAsString($iteration + 1), $color);
        $this->printOutlineExampleResultExceptions($examples, $this->delayedStepEvents);
    }

    /**
     * Prints outline example exceptions.
     *
     * @param   Behat\Gherkin\Node\TableNode    $examples   examples table
     * @param   array                           $events     failed steps events
     */
    protected function printOutlineExampleResultExceptions(TableNode $examples, array $events)
    {
        foreach ($events as $event) {
            if (null !== ($exception = $event->getException())) {
                $color = $this->getResultColorCode($event->getResult());

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

    /**
     * Prints scenario header.
     *
     * @param   Behat\Gherkin\Node\ScenarioNode $scenario
     *
     * @uses    printFeatureOrScenarioTags()
     * @uses    printScenarioName()
     * @uses    printScenarioPath()
     */
    protected function printScenarioHeader(ScenarioNode $scenario)
    {
        $this->maxLineLength = $this->getMaxLineLength($this->maxLineLength, $scenario);

        $this->printFeatureOrScenarioTags($scenario);
        $this->printScenarioName($scenario);
        $this->printScenarioPath($scenario);
    }

    /**
     * Prints scenario footer.
     *
     * @param   Behat\Gherkin\Node\ScenarioNode $scenario
     */
    protected function printScenarioFooter(ScenarioNode $scenario)
    {
        $this->writeln();
    }

    /**
     * Prints step.
     *
     * @param   Behat\Gherkin\Node\StepNode         $step       step node
     * @param   integer                             $result     result code
     * @param   Behat\Behat\Definition\Definition   $definition definition (if found one)
     * @param   string                              $snippet    snippet (if step is undefined)
     * @param   Exception                           $exception  exception (if step is failed)
     *
     * @uses    printStepBlock()
     * @uses    printStepArguments()
     * @uses    printStepException()
     * @uses    printStepSnippet()
     */
    protected function printStep(StepNode $step, $result, Definition $definition = null,
                                 $snippet = null, \Exception $exception = null)
    {
        $color = $this->getResultColorCode($result);

        $this->printStepBlock($step, $definition, $color);

        if ($this->parameters->get('multiline_arguments')) {
            $this->printStepArguments($step->getArguments(), $color);
        }
        if (null !== $exception) {
            $this->printStepException($exception, $color);
        }
        if (null !== $snippet) {
            $this->printStepSnippet($snippet);
        }
    }

    /**
     * Prints step block (name & definition path).
     *
     * @param   Behat\Gherkin\Node\StepNode         $step       step node
     * @param   Behat\Behat\Definition\Definition   $definition definition (if found one)
     * @param   string                              $color      color code
     *
     * @uses    printStepName()
     * @uses    printStepDefinitionPath()
     */
    protected function printStepBlock(StepNode $step, Definition $definition = null, $color)
    {
        $this->printStepName($step, $definition, $color);
        if (null !== $definition) {
            $this->printStepDefinitionPath($step, $definition);
        } else {
            $this->writeln();
        }
    }

    /**
     * Prints step name.
     *
     * @param   Behat\Gherkin\Node\StepNode         $step       step node
     * @param   Behat\Behat\Definition\Definition   $definition definition (if found one)
     * @param   string                              $color      color code
     *
     * @uses    colorizeDefinitionArguments()
     */
    protected function printStepName(StepNode $step, Definition $definition = null, $color)
    {
        $type   = $step->getType();
        $text   = $this->inOutlineSteps ? $step->getCleanText() : $step->getText();

        if (null !== $definition) {
            $text = $this->colorizeDefinitionArguments($text, $definition, $color);
        }

        $this->write("    {+$color}$type $text{-$color}");
    }

    /**
     * Prints step definition path.
     *
     * @param   Behat\Gherkin\Node\StepNode         $step       step node
     * @param   Behat\Behat\Definition\Definition   $definition definition (if found one)
     *
     * @uses    printPathComment()
     */
    protected function printStepDefinitionPath(StepNode $step, Definition $definition)
    {
        $type           = $step->getType();
        $text           = $this->inOutlineSteps ? $step->getCleanText() : $step->getText();
        $nameLength     = mb_strlen("    $type $text");
        $indentCount    = $nameLength > $this->maxLineLength ? 0 : $this->maxLineLength - $nameLength;

        $this->printPathComment($definition->getFile(), $definition->getLine(), $indentCount);
    }

    /**
     * Prints step arguments.
     *
     * @param   array   $arguments  step arguments
     * @param   string  $color      color name
     *
     * @uses    printStepPyStringArgument()
     * @uses    printStepTableArgument()
     */
    protected function printStepArguments(array $arguments, $color)
    {
        foreach ($arguments as $argument) {
            if ($argument instanceof PyStringNode) {
                $this->printStepPyStringArgument($argument, $color);
            } elseif ($argument instanceof TableNode) {
                $this->printStepTableArgument($argument, $color);
            }
        }
    }

    /**
     * Prints step exception.
     *
     * @param   Exception   $exception  exception
     * @param   string      $color      color name
     */
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

    /**
     * Prints step snippet
     *
     * @param   array   $snippet    snippet (for undefined steps)
     */
    protected function printStepSnippet(array $snippet)
    {
    }

    /**
     * Prints PyString argument.
     *
     * @param   Behat\Gherkin\Node\PyStringNode     $pystring   pystring node
     * @param   string                              $color      color name
     */
    protected function printStepPyStringArgument(PyStringNode $pystring, $color = null)
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

    /**
     * Prints table argument.
     *
     * @param   Behat\Gherkin\Node\TableNode        $table      table node
     * @param   string                              $color      color name
     */
    protected function printStepTableArgument(TableNode $table, $color = null)
    {
        $string = strtr('      ' . (string) $table, array("\n" => "\n      "));

        if (null !== $color) {
            $this->writeln("{+$color}$string{-$color}");
        } else {
            $this->writeln($string);
        }
    }

    /**
     * Prints table row in color.
     *
     * @param   array   $row    columns array
     * @param   string  $color  color code
     */
    protected function printColorizedTableRow($row, $color)
    {
        $string = preg_replace(
            '/|([^|]*)|/',
            "{+$color}\$1{-$color}",
            '      ' . $row
        );

        $this->writeln($string);
    }

    /**
     * Prints suite header.
     *
     * @param   Behat\Behat\DataCollector\LoggerDataCollector   $logger suite logger
     */
    protected function printSuiteHeader(LoggerDataCollector $logger)
    {
    }

    /**
     * Prints suite footer information.
     *
     * @param   Behat\Behat\DataCollector\LoggerDataCollector   $logger suite logger
     *
     * @uses    printSummary()
     * @uses    printUndefinedStepsSnippets()
     */
    protected function printSuiteFooter(LoggerDataCollector $logger)
    {
        $this->printSummary($logger);
        $this->printUndefinedStepsSnippets($logger);
    }

    /**
     * Returns feature or scenario name.
     *
     * @param   Behat\Gherkin\Node\AbstractNode     $node               node name
     * @param   boolean                             $haveBaseIndent     is name have base indent
     */
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
     * Returns step text with colorized arguments.
     *
     * @param   string                              $text
     * @param   Behat\Behat\Definition\Definition   $definition
     * @param   string                              $color
     *
     * @return  string
     */
    protected function colorizeDefinitionArguments($text, Definition $definition, $color)
    {
        $regex      = $definition->getRegex();
        $paramColor = $color . '_param';

        // Find arguments with offsets
        $matches = array();
        preg_match($regex, $text, $matches, PREG_OFFSET_CAPTURE);
        array_shift($matches);

        // Replace arguments with colorized ones
        $shift = 0;
        foreach ($matches as $key => $match) {
            if (!is_numeric($key) || -1 === $match[1] || '<' === $match[0][0]) {
                continue;
            }

            $offset = $match[1] + $shift;
            $value  = $match[0];
            $begin  = substr($text, 0, $offset);
            $end    = substr($text, $offset + strlen($value));
            // Keep track of how many extra characters are added
            $shift += strlen($format = "{-$color}{+$paramColor}%s{-$paramColor}{+$color}") - 2;

            $text = sprintf('%s' . $format. '%s', $begin, $value, $end);
        }

        // Replace "<", ">" with colorized ones
        $text = preg_replace('/(<[^>]+>)/', "{-$color}{+$paramColor}\$1{-$paramColor}{+$color}", $text);

        return $text;
    }

    /**
     * Returns max lines size for section elements.
     *
     * @param   Behat\Gherkin\Node\BackgroundNode   $scenario   element for calculations
     *
     * @return  integer
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

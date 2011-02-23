<?php

namespace Behat\Behat\Formatter;

use Symfony\Component\EventDispatcher\EventDispatcher,
    Symfony\Component\EventDispatcher\Event;

use Behat\Behat\Tester\StepTester,
    Behat\Behat\Definition\Definition,
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
     * {@inheritdoc}
     *
     * @uses    beforeSuite()
     * @uses    afterSuite()
     * @uses    beforeFeature()
     * @uses    afterFeature()
     * @uses    beforeBackground()
     * @uses    afterBackground()
     * @uses    beforeOutline()
     * @uses    beforeOutlineExample()
     * @uses    afterOutlineExample()
     * @uses    afterOutline()
     * @uses    beforeScenario()
     * @uses    afterScenario()
     * @uses    afterStep()
     */
    public function registerListeners(EventDispatcher $dispatcher)
    {
        $dispatcher->connect('suite.before',              array($this, 'beforeSuite'),            -10);
        $dispatcher->connect('suite.after',               array($this, 'afterSuite'),             -10);
        $dispatcher->connect('feature.before',            array($this, 'beforeFeature'),          -10);
        $dispatcher->connect('feature.after',             array($this, 'afterFeature'),           -10);
        $dispatcher->connect('background.before',         array($this, 'beforeBackground'),       -10);
        $dispatcher->connect('background.after',          array($this, 'afterBackground'),        -10);
        $dispatcher->connect('outline.before',            array($this, 'beforeOutline'),          -10);
        $dispatcher->connect('outline.example.before',    array($this, 'beforeOutlineExample'),   -10);
        $dispatcher->connect('outline.example.after',     array($this, 'afterOutlineExample'),    -10);
        $dispatcher->connect('outline.after',             array($this, 'afterOutline'),           -10);
        $dispatcher->connect('scenario.before',           array($this, 'beforeScenario'),         -10);
        $dispatcher->connect('scenario.after',            array($this, 'afterScenario'),          -10);
        $dispatcher->connect('step.after',                array($this, 'afterStep'),              -10);
    }

    /**
     * Listens to "suite.before" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     *
     * @uses    printSuiteHeader()
     */
    public function beforeSuite(Event $event)
    {
        $logger = $event->getSubject();

        $this->printSuiteHeader($logger);
    }

    /**
     * Listens to "suite.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     *
     * @uses    printSuiteFooter()
     */
    public function afterSuite(Event $event)
    {
        $logger = $event->getSubject();

        $this->printSuiteFooter($logger);
    }

    /**
     * Listens to "feature.before" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     *
     * @uses    printFeatureHeader()
     */
    public function beforeFeature(Event $event)
    {
        $feature = $event->getSubject();

        $this->isBackgroundPrinted = false;

        $this->printFeatureHeader($feature);
    }

    /**
     * Listens to "feature.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     *
     * @uses    printFeatureFooter()
     */
    public function afterFeature(Event $event)
    {
        $feature = $event->getSubject();

        $this->printFeatureFooter($feature);
    }

    /**
     * Listens to "background.before" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     *
     * @uses    printBackgroundHeader()
     */
    public function beforeBackground(Event $event)
    {
        $this->inBackground = true;

        if ($this->isBackgroundPrinted) {
            return;
        }

        $background = $event->getSubject();

        $this->printBackgroundHeader($background);
    }

    /**
     * Listens to "background.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     *
     * @uses    printBackgroundFooter()
     */
    public function afterBackground(Event $event)
    {
        $this->inBackground = false;

        if ($this->isBackgroundPrinted) {
            return;
        }
        $this->isBackgroundPrinted = true;

        $background = $event->getSubject();

        $this->printBackgroundFooter($background);

        if (null !== $this->delayedScenarioEvent) {
            $method = $this->delayedScenarioEvent[0];
            $event  = $this->delayedScenarioEvent[1];

            $this->$method($event);
        }
    }

    /**
     * Listens to "outline.before" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     *
     * @uses    printOutlineHeader()
     */
    public function beforeOutline(Event $event)
    {
        $outline = $event->getSubject();

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
     * @param   Symfony\Component\EventDispatcher\Event     $event
     *
     * @uses    printOutlineExampleHeader()
     */
    public function beforeOutlineExample(Event $event)
    {
        $this->inOutlineExample     = true;
        $this->delayedStepEvents    = array();

        $outline = $event->getSubject();

        $this->printOutlineExampleHeader($outline, $event->get('iteration'));
    }

    /**
     * Listens to "outline.example.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     *
     * @uses    printOutlineExampleFooter()
     */
    public function afterOutlineExample(Event $event)
    {
        $this->inOutlineExample = false;

        $outline = $event->getSubject();

        $this->printOutlineExampleFooter(
            $outline, $event->get('iteration'), $event->get('result'), $event->get('skipped')
        );
    }

    /**
     * Listens to "outline.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     *
     * @uses    printOutlineFooter()
     */
    public function afterOutline(Event $event)
    {
        $outline = $event->getSubject();

        $this->printOutlineFooter($outline);
    }

    /**
     * Listens to "scenario.before" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     *
     * @uses    printScenarioHeader()
     */
    public function beforeScenario(Event $event)
    {
        $scenario = $event->getSubject();

        if (!$this->isBackgroundPrinted && $scenario->getFeature()->hasBackground()) {
            $this->delayedScenarioEvent = array(__FUNCTION__, $event);

            return;
        }

        $this->printScenarioHeader($scenario);
    }

    /**
     * Listens to "scenario.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     *
     * @uses    printScenarioFooter()
     */
    public function afterScenario(Event $event)
    {
        $scenario = $event->getSubject();

        $this->printScenarioFooter($scenario);
    }

    /**
     * Listens to "step.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     *
     * @uses    printStep()
     */
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
            $step       = $event->getSubject();
            $definition = $event->get('definition');

            $this->printStep($step, StepTester::SKIPPED, $definition);
        }

        $this->inOutlineSteps = false;

        $this->writeln();
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
        foreach ($matches as $match) {
            $offset = $match[1];
            $value  = $match[0];
            $begin  = substr($text, 0, $offset);
            $end    = substr($text, $offset + strlen($value));

            $text = "$begin{-$color}{+$paramColor}$value{-$paramColor}{+$color}$end";
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

<?php

namespace Behat\Behat\Formatter;

use Behat\Behat\Definition\DefinitionInterface,
    Behat\Behat\Definition\DefinitionSnippet,
    Behat\Behat\DataCollector\LoggerDataCollector,
    Behat\Behat\Event\SuiteEvent,
    Behat\Behat\Event\FeatureEvent,
    Behat\Behat\Event\ScenarioEvent,
    Behat\Behat\Event\BackgroundEvent,
    Behat\Behat\Event\OutlineEvent,
    Behat\Behat\Event\OutlineExampleEvent,
    Behat\Behat\Event\StepEvent,
    Behat\Behat\Event\EventInterface,
    Behat\Behat\Exception\UndefinedException;

use Behat\Gherkin\Node\AbstractNode,
    Behat\Gherkin\Node\FeatureNode,
    Behat\Gherkin\Node\BackgroundNode,
    Behat\Gherkin\Node\AbstractScenarioNode,
    Behat\Gherkin\Node\OutlineNode,
    Behat\Gherkin\Node\ScenarioNode,
    Behat\Gherkin\Node\StepNode,
    Behat\Gherkin\Node\ExampleStepNode,
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
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PrettyFormatter extends ProgressFormatter
{
    /**
     * Maximum line length.
     *
     * @var integer
     */
    protected $maxLineLength          = 0;
    /**
     * Are we in background.
     *
     * @var Boolean
     */
    protected $inBackground           = false;
    /**
     * Is background printed.
     *
     * @var Boolean
     */
    protected $isBackgroundPrinted    = false;
    /**
     * Are we in outline steps.
     *
     * @var Boolean
     */
    protected $inOutlineSteps         = false;
    /**
     * Are we in outline example.
     *
     * @var Boolean
     */
    protected $inOutlineExample       = false;
    /**
     * Is outline headline printed.
     *
     * @var Boolean
     */
    protected $isOutlineHeaderPrinted = false;
    /**
     * Delayed scenario event.
     *
     * @var EventInterface
     */
    protected $delayedScenarioEvent;
    /**
     * Delayed step events.
     *
     * @var array
     */
    protected $delayedStepEvents      = array();
    /**
     * Current step indentation.
     *
     * @var integer
     */
    protected $stepIndent             = '    ';

    /**
     * {@inheritdoc}
     */
    protected function getDefaultParameters()
    {
        return array();
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        $events = array(
            'beforeSuite', 'afterSuite', 'beforeFeature', 'afterFeature', 'beforeScenario',
            'afterScenario', 'beforeBackground', 'afterBackground', 'beforeOutline', 'afterOutline',
            'beforeOutlineExample', 'afterOutlineExample', 'afterStep'
        );

        return array_combine($events, $events);
    }

    /**
     * Listens to "suite.before" event.
     *
     * @param SuiteEvent $event
     *
     * @uses printSuiteHeader()
     */
    public function beforeSuite(SuiteEvent $event)
    {
        $this->printSuiteHeader($event->getLogger());
    }

    /**
     * Listens to "suite.after" event.
     *
     * @param SuiteEvent $event
     *
     * @uses printSuiteFooter()
     */
    public function afterSuite(SuiteEvent $event)
    {
        $this->printSuiteFooter($event->getLogger());
    }

    /**
     * Listens to "feature.before" event.
     *
     * @param FeatureEvent $event
     *
     * @uses printFeatureHeader()
     */
    public function beforeFeature(FeatureEvent $event)
    {
        $this->isBackgroundPrinted = false;
        $this->printFeatureHeader($event->getFeature());
    }

    /**
     * Listens to "feature.after" event.
     *
     * @param FeatureEvent $event
     *
     * @uses printFeatureFooter()
     */
    public function afterFeature(FeatureEvent $event)
    {
        $this->printFeatureFooter($event->getFeature());
    }

    /**
     * Listens to "background.before" event.
     *
     * @param BackgroundEvent $event
     *
     * @uses printBackgroundHeader()
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
     * @param BackgroundEvent $event
     *
     * @uses printBackgroundFooter()
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
     * @param OutlineEvent $event
     *
     * @uses printOutlineHeader()
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
     * @param OutlineExampleEvent $event
     *
     * @uses printOutlineExampleHeader()
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
     * @param OutlineExampleEvent $event
     *
     * @uses printOutlineExampleFooter()
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
     * @param OutlineEvent $event
     *
     * @uses printOutlineFooter()
     */
    public function afterOutline(OutlineEvent $event)
    {
        $this->printOutlineFooter($event->getOutline());
    }

    /**
     * Listens to "scenario.before" event.
     *
     * @param ScenarioEvent $event
     *
     * @uses printScenarioHeader()
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
     * @param ScenarioEvent $event
     *
     * @uses printScenarioFooter()
     */
    public function afterScenario(ScenarioEvent $event)
    {
        $this->printScenarioFooter($event->getScenario());
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
     * @param FeatureNode $feature
     *
     * @uses printFeatureOrScenarioTags()
     * @uses printFeatureName()
     * @uses printFeatureDescription()
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
     * @param AbstractNode $node
     */
    protected function printFeatureOrScenarioTags(AbstractNode $node)
    {
        if (count($tags = $node->getOwnTags())) {
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
     * @param FeatureNode $feature
     *
     * @uses getFeatureOrScenarioName()
     */
    protected function printFeatureName(FeatureNode $feature)
    {
        $this->writeln($this->getFeatureOrScenarioName($feature));
    }

    /**
     * Prints feature description.
     *
     * @param FeatureNode $feature
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
     * @param FeatureNode $feature
     */
    protected function printFeatureFooter(FeatureNode $feature)
    {
    }

    /**
     * Prints scenario keyword and name.
     *
     * @param AbstractScenarioNode $scenario
     *
     * @uses getFeatureOrScenarioName()
     * @uses printScenarioPath()
     */
    protected function printScenarioName(AbstractScenarioNode $scenario)
    {
        $title = explode("\n", $this->getFeatureOrScenarioName($scenario));

        $this->write(array_shift($title));
        $this->printScenarioPath($scenario);

        if (count($title)) {
            $this->writeln(implode("\n", $title));
        }
    }

    /**
     * Prints scenario definition path.
     *
     * @param AbstractScenarioNode $scenario
     *
     * @uses getFeatureOrScenarioName()
     * @uses printPathComment()
     */
    protected function printScenarioPath(AbstractScenarioNode $scenario)
    {
        if ($this->getParameter('paths')) {
            $lines       = explode("\n", $this->getFeatureOrScenarioName($scenario));
            $nameLength  = mb_strlen(current($lines));
            $indentCount = $nameLength > $this->maxLineLength ? 0 : $this->maxLineLength - $nameLength;

            $this->printPathComment(
                $this->relativizePathsInString($scenario->getFile()).':'.$scenario->getLine(), $indentCount
            );
        } else {
            $this->writeln();
        }
    }

    /**
     * Prints background header.
     *
     * @param BackgroundNode $background
     *
     * @uses printScenarioName()
     * @uses printScenarioPath()
     */
    protected function printBackgroundHeader(BackgroundNode $background)
    {
        $this->maxLineLength = $this->getMaxLineLength($this->maxLineLength, $background);

        $this->printScenarioName($background);
    }

    /**
     * Prints background footer.
     *
     * @param BackgroundNode $background
     */
    protected function printBackgroundFooter(BackgroundNode $background)
    {
        $this->writeln();
    }

    /**
     * Prints outline header.
     *
     * @param OutlineNode $outline
     *
     * @uses printFeatureOrScenarioTags()
     * @uses printScenarioName()
     */
    protected function printOutlineHeader(OutlineNode $outline)
    {
        $this->maxLineLength = $this->getMaxLineLength($this->maxLineLength, $outline);

        $this->printFeatureOrScenarioTags($outline);
        $this->printScenarioName($outline);
    }

    /**
     * Prints outline footer.
     *
     * @param OutlineNode $outline
     */
    protected function printOutlineFooter(OutlineNode $outline)
    {
        $this->writeln();
    }

    /**
     * Prints outline example header.
     *
     * @param OutlineNode $outline
     * @param integer     $iteration
     */
    protected function printOutlineExampleHeader(OutlineNode $outline, $iteration)
    {
    }

    /**
     * Prints outline example result.
     *
     * @param OutlineNode $outline   outline instance
     * @param integer     $iteration example row number
     * @param integer     $result    result code
     * @param Boolean     $skipped   is outline example skipped
     *
     * @uses printOutlineSteps()
     * @uses printOutlineExamplesSectionHeader()
     * @uses printOutlineExampleResult()
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
     * @param OutlineNode $outline
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
     * @param TableNode $examples
     *
     * @uses printColorizedTableRow()
     */
    protected function printOutlineExamplesSectionHeader(TableNode $examples)
    {
        $this->writeln();
        $keyword = $examples->getKeyword();

        if (!$this->getParameter('expand')) {
            $this->writeln("    $keyword:");
            $this->printColorizedTableRow($examples->getRowAsString(0), 'skipped');
        }
    }

    /**
     * Prints outline example result.
     *
     * @param TableNode $examples  examples table
     * @param integer   $iteration example row
     * @param integer   $result    result code
     * @param boolean   $isSkipped is outline example skipped
     *
     * @uses printColorizedTableRow()
     * @uses printOutlineExampleResultExceptions()
     */
    protected function printOutlineExampleResult(TableNode $examples, $iteration, $result, $isSkipped)
    {
        if (!$this->getParameter('expand')) {
            $color = $this->getResultColorCode($result);

            $this->printColorizedTableRow($examples->getRowAsString($iteration + 1), $color);
            $this->printOutlineExampleResultExceptions($examples, $this->delayedStepEvents);
        } else {
            $this->write('      ' . $examples->getKeyword() . ': ');
            $this->writeln('| ' . implode(' | ', $examples->getRow($iteration + 1)) . ' |');

            $this->stepIndent = '        ';
            foreach ($this->delayedStepEvents as $event) {
                $this->printStep(
                    $event->getStep(),
                    $event->getResult(),
                    $event->getDefinition(),
                    $event->getSnippet(),
                    $event->getException()
                );
            }
            $this->stepIndent = '    ';

            if ($iteration < count($examples->getRows()) - 2) {
                $this->writeln();
            }
        }
    }

    /**
     * Prints outline example exceptions.
     *
     * @param TableNode $examples examples table
     * @param array     $events   failed steps events
     */
    protected function printOutlineExampleResultExceptions(TableNode $examples, array $events)
    {
        foreach ($events as $event) {
            $exception = $event->getException();
            if ($exception && !$exception instanceof UndefinedException) {
                $color = $this->getResultColorCode($event->getResult());

                $error = $this->exceptionToString($exception);
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
     * @param ScenarioNode $scenario
     *
     * @uses printFeatureOrScenarioTags()
     * @uses printScenarioName()
     */
    protected function printScenarioHeader(ScenarioNode $scenario)
    {
        $this->maxLineLength = $this->getMaxLineLength($this->maxLineLength, $scenario);

        $this->printFeatureOrScenarioTags($scenario);
        $this->printScenarioName($scenario);
    }

    /**
     * Prints scenario footer.
     *
     * @param ScenarioNode $scenario
     */
    protected function printScenarioFooter(ScenarioNode $scenario)
    {
        $this->writeln();
    }

    /**
     * Prints step.
     *
     * @param StepNode            $step       step node
     * @param integer             $result     result code
     * @param DefinitionInterface $definition definition (if found one)
     * @param string              $snippet    snippet (if step is undefined)
     * @param \Exception          $exception  exception (if step is failed)
     *
     * @uses printStepBlock()
     * @uses printStepArguments()
     * @uses printStepException()
     * @uses printStepSnippet()
     */
    protected function printStep(StepNode $step, $result, DefinitionInterface $definition = null,
                                 $snippet = null, \Exception $exception = null)
    {
        $color = $this->getResultColorCode($result);

        $this->printStepBlock($step, $definition, $color);

        if ($this->parameters->get('multiline_arguments')) {
            $this->printStepArguments($step->getArguments(), $color);
        }
        if (null !== $exception &&
            (!$exception instanceof UndefinedException || null === $snippet)) {
            $this->printStepException($exception, $color);
        }
        if (null !== $snippet && $this->getParameter('snippets')) {
            $this->printStepSnippet($snippet);
        }
    }

    /**
     * Prints step block (name & definition path).
     *
     * @param StepNode            $step       step node
     * @param DefinitionInterface $definition definition (if found one)
     * @param string              $color      color code
     *
     * @uses printStepName()
     * @uses printStepDefinitionPath()
     */
    protected function printStepBlock(StepNode $step, DefinitionInterface $definition = null, $color)
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
     * @param StepNode            $step       step node
     * @param DefinitionInterface $definition definition (if found one)
     * @param string              $color      color code
     *
     * @uses colorizeDefinitionArguments()
     */
    protected function printStepName(StepNode $step, DefinitionInterface $definition = null, $color)
    {
        $type   = $step->getType();
        $text   = $this->inOutlineSteps ? $step->getCleanText() : $step->getText();
        $indent = $this->stepIndent;

        if (null !== $definition) {
            $text = $this->colorizeDefinitionArguments($text, $definition, $color);
        }

        $this->write("$indent{+$color}$type $text{-$color}");
    }

    /**
     * Prints step definition path.
     *
     * @param StepNode            $step       step node
     * @param DefinitionInterface $definition definition (if found one)
     *
     * @uses printPathComment()
     */
    protected function printStepDefinitionPath(StepNode $step, DefinitionInterface $definition)
    {
        if ($this->getParameter('paths')) {
            $type           = $step->getType();
            $text           = $this->inOutlineSteps ? $step->getCleanText() : $step->getText();
            $indent         = $this->stepIndent;
            $nameLength     = mb_strlen("$indent$type $text");
            $indentCount    = $nameLength > $this->maxLineLength ? 0 : $this->maxLineLength - $nameLength;

            $this->printPathComment(
                $this->relativizePathsInString($definition->getPath()), $indentCount
            );

            if ($this->getParameter('expand')) {
                $this->maxLineLength = max($this->maxLineLength, $nameLength);
            }
        } else {
            $this->writeln();
        }
    }

    /**
     * Prints step arguments.
     *
     * @param array  $arguments step arguments
     * @param string $color     color name
     *
     * @uses printStepPyStringArgument()
     * @uses printStepTableArgument()
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
     * @param \Exception $exception
     * @param string     $color
     */
    protected function printStepException(\Exception $exception, $color)
    {
        $indent = $this->stepIndent;

        $error = $this->exceptionToString($exception);
        $error = $this->relativizePathsInString($error);

        $this->writeln(
            "$indent  {+$color}" . strtr($error, array("\n" => "\n$indent  ")) . "{-$color}"
        );
    }

    /**
     * Prints step snippet
     *
     * @param DefinitionSnippet $snippet
     */
    protected function printStepSnippet(DefinitionSnippet $snippet)
    {
    }

    /**
     * Prints PyString argument.
     *
     * @param PyStringNode $pystring pystring node
     * @param string       $color    color name
     */
    protected function printStepPyStringArgument(PyStringNode $pystring, $color = null)
    {
        $indent = $this->stepIndent;
        $string = strtr(
            sprintf("$indent  \"\"\"\n%s\n\"\"\"", (string) $pystring), array("\n" => "\n$indent  ")
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
     * @param TableNode $table
     * @param string    $color
     */
    protected function printStepTableArgument(TableNode $table, $color = null)
    {
        $indent = $this->stepIndent;
        $string = strtr("$indent  " . (string) $table, array("\n" => "\n$indent  "));

        if (null !== $color) {
            $this->writeln("{+$color}$string{-$color}");
        } else {
            $this->writeln($string);
        }
    }

    /**
     * Prints table row in color.
     *
     * @param array  $row
     * @param string $color
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
     * @param LoggerDataCollector $logger suite logger
     */
    protected function printSuiteHeader(LoggerDataCollector $logger)
    {
    }

    /**
     * Prints suite footer information.
     *
     * @param LoggerDataCollector $logger suite logger
     *
     * @uses printSummary()
     * @uses printUndefinedStepsSnippets()
     */
    protected function printSuiteFooter(LoggerDataCollector $logger)
    {
        $this->printSummary($logger);
        $this->printUndefinedStepsSnippets($logger);
    }

    /**
     * Returns feature or scenario name.
     *
     * @param AbstractNode $node
     * @param Boolean      $haveBaseIndent
     *
     * @return string
     */
    protected function getFeatureOrScenarioName(AbstractNode $node, $haveBaseIndent = true)
    {
        $keyword    = $node->getKeyword();
        $baseIndent = ($node instanceof FeatureNode) || !$haveBaseIndent ? '' : '  ';

        $lines = explode("\n", $node->getTitle());
        $title = array_shift($lines);

        if (count($lines)) {
            foreach ($lines as $line) {
                $title .= "\n" . $baseIndent.'  '.$line;
            }
        }

        return "$baseIndent$keyword:" . ($title ? ' ' . $title : '');
    }

    /**
     * Returns step text with colorized arguments.
     *
     * @param string              $text
     * @param DefinitionInterface $definition
     * @param string              $color
     *
     * @return string
     */
    protected function colorizeDefinitionArguments($text, DefinitionInterface $definition, $color)
    {
        $regex      = $definition->getRegex();
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
            $value  = $match[0];

            // Skip inner matches
            if ($lastReplacementPosition > $offset) {
                continue;
            }
            $lastReplacementPosition = $offset + strlen($value);

            $begin  = substr($text, 0, $offset);
            $end    = substr($text, $lastReplacementPosition);
            $format = "{-$color}{+$paramColor}%s{-$paramColor}{+$color}";
            $text   = sprintf("%s{$format}%s", $begin, $value, $end);

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
     * Returns max lines size for section elements.
     *
     * @param integer              $max      previous max value
     * @param AbstractScenarioNode $scenario element for calculations
     *
     * @return integer
     */
    protected function getMaxLineLength($max, AbstractScenarioNode $scenario)
    {
        $lines = explode("\n", $this->getFeatureOrScenarioName($scenario, false));
        $max   = max($max, mb_strlen(current($lines)) + 2);

        foreach ($scenario->getSteps() as $step) {
            $text = $step instanceof ExampleStepNode ? $step->getCleanText() : $step->getText();
            $stepDescription = $step->getType() . ' ' . $text;
            $max = max($max, mb_strlen($stepDescription) + 4);
        }

        return $max;
    }
}

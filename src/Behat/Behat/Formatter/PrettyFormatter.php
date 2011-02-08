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
     * @uses    afterSuite()
     * @uses    beforeFeature()
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
        $dispatcher->connect('suite.after',               array($this, 'afterSuite'),             -10);
        $dispatcher->connect('feature.before',            array($this, 'beforeFeature'),          -10);
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
     * Listens to "suite.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     *
     * @uses    printSummary()
     * @uses    printUndefinedStepsSnippets()
     */
    public function afterSuite(Event $event)
    {
        $logger = $event->getSubject();

        $this->printSummary($logger);
        $this->printUndefinedStepsSnippets($logger);
    }

    /**
     * Listens to "feature.before" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     *
     * @uses    printFeatureOrScenarioTags()
     * @uses    printFeatureName()
     * @uses    printFeatureDescription()
     */
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

    /**
     * Listens to "background.before" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     *
     * @uses    printScenarioName()
     * @uses    printScenarioPath()
     */
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

    /**
     * Listens to "background.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     */
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

    /**
     * Listens to "outline.before" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     *
     * @uses    printFeatureOrScenarioTags()
     * @uses    printScenarioName()
     * @uses    printScenarioPath()
     */
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

    /**
     * Listens to "outline.example.before" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     */
    public function beforeOutlineExample(Event $event)
    {
        $this->inOutlineExample     = true;
        $this->delayedStepEvents    = array();
    }

    /**
     * Listens to "outline.example.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     *
     * @uses    printOutlineSteps()
     * @uses    printOutlineExamplesHeader()
     * @uses    printOutlineExampleResult()
     */
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

    /**
     * Listens to "outline.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     */
    public function afterOutline(Event $event)
    {
        $this->writeln();
    }

    /**
     * Listens to "scenario.before" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     *
     * @uses    printFeatureOrScenarioTags()
     * @uses    printScenarioName()
     * @uses    printScenarioPath()
     */
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

    /**
     * Listens to "scenario.after" event.
     *
     * @param   Symfony\Component\EventDispatcher\Event     $event
     */
    public function afterScenario(Event $event)
    {
        $this->writeln();
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

            $this->writeln("$indent{+tags}$tags{-tags}");
        }
    }

    /**
     * Prints feature keyword and name.
     *
     * @param   Behat\Gherkin\Node\FeatureNode  $feature
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
     * Prints scenario keyword and name.
     *
     * @param   Behat\Gherkin\Node\AbstractScenarioNode $scenario
     */
    protected function printScenarioName(AbstractScenarioNode $scenario)
    {
        $this->write($this->getFeatureOrScenarioName($scenario));
    }

    /**
     * Prints scenario definition path.
     *
     * @param   Behat\Gherkin\Node\AbstractScenarioNode $scenario
     */
    protected function printScenarioPath(AbstractScenarioNode $scenario)
    {
        $nameLength     = mb_strlen($this->getFeatureOrScenarioName($scenario));
        $indentCount    = $nameLength > $this->maxLineLength ? 0 : $this->maxLineLength - $nameLength;

        $this->printPathComment($scenario->getFile(), $scenario->getLine(), $indentCount);
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
     */
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

    /**
     * Prints outline example result.
     *
     * @param   Behat\Gherkin\Node\TableNode    $examples   examples table
     * @param   integer                         $iteration  example row
     * @param   integer                         $result     result code
     * @param   boolean                         $isSkipped  is outline example skipped
     */
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

    /**
     * Prints outline example exceptions.
     *
     * @param   array   $events failed steps events
     */
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

    /**
     * Prints step.
     *
     * @param   Behat\Gherkin\Node\StepNode         $step       step node
     * @param   integer                             $result     result code
     * @param   Behat\Behat\Definition\Definition   $definition definition (if found one)
     * @param   string                              $snippet    snippet (if step is undefined)
     * @param   Exception                           $exception  exception (if step is failed)
     */
    protected function printStep(StepNode $step, $result, Definition $definition = null,
                                 $snippet = null, \Exception $exception = null)
    {
        $type   = $step->getType();
        $text   = $this->inOutlineSteps ? $step->getCleanText() : $step->getText();
        $color  = $this->getResultColorCode($result);

        $this->write("    {+$color}$type $text{-$color}");

        if (null !== $definition) {
            $this->printDefinitionPath($step, $definition);
        } else {
            $this->writeln();
        }

        $this->printStepArguments($step->getArguments(), $color);

        if (null !== $exception) {
            $this->printStepException($exception, $color);
        }
    }

    /**
     * Prints step definition path.
     *
     * @param   Behat\Gherkin\Node\StepNode         $step       step node
     * @param   Behat\Behat\Definition\Definition   $definition definition (if found one)
     */
    protected function printDefinitionPath(StepNode $step, Definition $definition)
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
     */
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
     * Prints PyString argument.
     *
     * @param   Behat\Gherkin\Node\PyStringNode     $pystring   pystring node
     * @param   string                              $color      color name
     */
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

    /**
     * Prints table argument.
     *
     * @param   Behat\Gherkin\Node\TableNode        $table      table node
     * @param   string                              $color      color name
     */
    protected function printTable(TableNode $table, $color = null)
    {
        $string = strtr('      ' . (string) $table, array("\n" => "\n      "));

        if (null !== $color) {
            $this->writeln("{+$color}$string{-$color}");
        } else {
            $this->writeln($string);
        }
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

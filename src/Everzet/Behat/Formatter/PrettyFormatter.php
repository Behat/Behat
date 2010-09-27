<?php

namespace Everzet\Behat\Formatter;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Gherkin\Node\FeatureNode;
use Everzet\Gherkin\Node\StepNode;
use Everzet\Gherkin\Node\BackgroundNode;
use Everzet\Gherkin\Node\SectionNode;
use Everzet\Gherkin\Node\ScenarioNode;
use Everzet\Gherkin\Node\OutlineNode;
use Everzet\Gherkin\Node\PyStringNode;
use Everzet\Gherkin\Node\TableNode;
use Everzet\Gherkin\Node\ExamplesNode;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Console pretty output formatter.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PrettyFormatter extends ConsoleFormatter implements FormatterInterface
{
    protected $container;
    protected $output;
    protected $verbose;
    protected $maxDescriptionLength = 0;

    /**
     * @see Everzet\Behat\Formatter\FormatterInterface
     */
    public function __construct(Container $container)
    {
        $this->container    = $container;
        $this->output       = $container->getOutputService();
        $this->verbose      = $container->getParameter('formatter.verbose');

        $this->output->setStyle('failed',      array('fg' => 'red'));
        $this->output->setStyle('undefined',   array('fg' => 'yellow'));
        $this->output->setStyle('pending',     array('fg' => 'yellow'));
        $this->output->setStyle('passed',      array('fg' => 'green'));
        $this->output->setStyle('skipped',     array('fg' => 'cyan'));
        $this->output->setStyle('comment',     array('fg' => 'black'));
        $this->output->setStyle('tag',         array('fg' => 'cyan'));
    }

    /**
     * @see Everzet\Behat\Formatter\FormatterInterface
     */
    public function registerListeners(EventDispatcher $dispatcher)
    {
        $this->registerRunCounters($dispatcher);

        $dispatcher->connect('feature.run.before',      array($this, 'printFeatureHeader'),     10);

        $dispatcher->connect('outline.run.before',      array($this, 'printOutlineHeader'),     10);
        $dispatcher->connect('outline.run.after',       array($this, 'printOutlineFooter'),     10);

        $dispatcher->connect('scenario.run.before',     array($this, 'printScenarioHeader'),    10);
        $dispatcher->connect('scenario.run.after',      array($this, 'printScenarioFooter'),    10);

        $dispatcher->connect('background.run.before',   array($this, 'printBackgroundHeader'),  10);
        $dispatcher->connect('background.run.after',    array($this, 'printBackgroundFooter'),  10);

        $dispatcher->connect('step.run.after',          array($this, 'printStep'),              10);
        $dispatcher->connect('step.skip.after',         array($this, 'printStep'),              10);

        $dispatcher->connect('suite.run.after',         array($this, 'printStatistics'),        10);
        $dispatcher->connect('suite.run.after',         array($this, 'printSnippets'),          10);
    }

    /**
     * Listens to `feature.pre_test` event & prints feature header (title & description)
     *
     * @param   Event   $event  notified event
     */
    public function printFeatureHeader(Event $event)
    {
        $feature = $event->getSubject();

        // Print tags if had ones
        if ($feature->hasTags()) {
            $this->write($this->getTagsString($feature), 'tag');
        }

        // Print feature title
        $this->write($feature->getI18n()->__('feature', 'Feature') . ': ' . $feature->getTitle());

        // Print feature description
        foreach ($feature->getDescription() as $description) {
            $this->write('  ' . $description);
        }
        $this->write();

        // Run fake background to test if it runs without errors & prints it output
        if ($feature->hasBackground()) {
            $background = $feature->getBackground();
            $background->setPrintable();
            $background->run($this->container, $this->container->getEnvironmentService());
            $background->setPrintable(false);
        }
    }

    /**
      * Listens to `scenario_outline.pre_test` event & prints outline header (title & description)
      *
      * @param   Event   $event  notified event
      */
    public function printOutlineHeader(Event $event)
    {
        $outline = $event->getSubject();

        // Recalc maximum description length (for filepath-like comments)
        $this->recalcMaxDescriptionLength($outline);

        // Print tags if had ones
        if ($outline->hasTags()) {
            $this->write($this->getTagsString($outline), 'tag');
        }

        // Print outline description
        $description = sprintf("  %s:%s",
            $outline->getI18n()->__('scenario-outline', 'Scenario Outline'),
            $outline->getTitle() ? ' ' . $outline->getTitle() : ''
        );
        $this->write($description, null, false);

        // Print element path & line
        $this->printLineSourceComment(
            mb_strlen($description)
          , $outline->getFile()
          , $outline->getLine()
        );
    }

    /**
      * Listens to `scenario_outline.post_test` event & prints outline footer (newline after)
      *
      * @param   Event   $event  notified event
      */
    public function printOutlineFooter(Event $event)
    {
        $this->write();
    }

    /**
      * Listens to `scenario.pre_test` event & prints scenario header (title & description)
      *
      * @param   Event   $event  notified event
      */
    public function printScenarioHeader(Event $event)
    {
        $scenario = $event->getSubject();

        // Recalc maximum description length (for filepath-like comments)
        $this->recalcMaxDescriptionLength($scenario);

        if (!$scenario->isInOutline()) {
            // Print tags if had ones
            if ($scenario->hasTags()) {
                $this->write($this->getTagsString($scenario), 'tag');
            }

            // Print scenario description
            $description = sprintf("  %s:%s",
                $scenario->getI18n()->__('scenario', 'Scenario'),
                $scenario->getTitle() ? ' ' . $scenario->getTitle() : ''
            );
            $this->write($description, null, false);

            // Print element path & line
            $this->printLineSourceComment(
                mb_strlen($description)
              , $scenario->getFile()
              , $scenario->getLine()
            );
        }
    }

    /**
      * Listens to `scenario.post_test` event & prints scenario footer (newline or outline row)
      *
      * @param   Event   $event  notified event
      */
    public function printScenarioFooter(Event $event)
    {
        $scenario = $event->getSubject();

        if (!$scenario->isInOutline()) {
            $this->write();
        } else {
            $outline    = $scenario->getOutline();
            $examples   = $outline->getExamples()->getTable();

            // Print outline description with steps & examples after first scenario in batch runned
            if (0 === $outline->getFinishedScenariosCount()) {

                // Print outline steps
                foreach ($outline->getSteps() as $step) {
                    // Print step description
                    $description = sprintf('    %s %s', $step->getType(), $step->getCleanText());
                    $this->write($description, 'skipped', false);

                    // Print definition/element path
                    if (null !== $step->getDefinition()) {
                        $this->printLineSourceComment(
                            mb_strlen($description)
                          , $step->getDefinition()->getFile()
                          , $step->getDefinition()->getLine()
                        );
                    } else {
                        $this->write();
                    }
                }

                // Print outline examples title
                $this->write(sprintf("\n    %s:", $outline->getI18n()->__('examples', 'Examples')));

                // print outline examples header row
                $this->write(
                    preg_replace(
                        '/|([^|]*)|/'
                      , $this->colorize('$1', 'skipped')
                      , '      ' . $examples->getKeysAsString()
                    )
                );
            }

            // print current scenario results row
            $this->write(
                preg_replace(
                    '/|([^|]*)|/'
                  , $this->colorize('$1', $scenario->getResult())
                  , '      ' . $examples->getRowAsString($outline->getFinishedScenariosCount())
                )
            );

            // Print errors
            foreach ($scenario->getSteps() as $step) {
                if (null !== $step->getException()) {
                    if ($this->verbose) {
                        $error = (string) $step->getException();
                    } else {
                        $error = $step->getException()->getMessage();
                    }
                    $this->write('        ' . strtr($error, array("\n" => "\n      ")), 'failed');
                }
            }
        }
    }

    /**
      * Listens to `background.pre_test` event & prints background header (if needed)
      *
      * @param   Event   $event  notified event
      */
    public function printBackgroundHeader(Event $event)
    {
        $background = $event->getSubject();

        // Recalc maximum description length (for filepath-like comments)
        $this->recalcMaxDescriptionLength($background);

        if ($background->isPrintable()) {
            // Print description
            $description = sprintf("  %s:%s",
                $background->getI18n()->__('background', 'Background'),
                $background->getTitle() ? ' ' . $background->getTitle() : ''
            );
            $this->write($description, null, false);

            // Print element path & line
            $this->printLineSourceComment(
                mb_strlen($description)
              , $background->getFile()
              , $background->getLine()
            );
        }
    }

    /**
      * Listens to `background.post_test` event & prints background footer (if needed)
      *
      * @param   Event   $event  notified event
      */
    public function printBackgroundFooter(Event $event)
    {
        $background = $event->getSubject();

        if ($background->isPrintable()) {
            $this->write();
        }
    }

    /**
      * Listens to `step.post_test` event & prints step runner information
      *
      * @param   Event   $event  notified event
      */
    public function printStep(Event $event)
    {
        $step = $event->getSubject();

        if ($step->isPrintable()) {
            // Print step description
            $description = sprintf('    %s %s', $step->getType(), $step->getText());
            $this->write($description, $step->getResult(), false);

            // Print definition path if found one
            if (null !== $step->getDefinition()) {
                $this->printLineSourceComment(
                    mb_strlen($description)
                  , $step->getDefinition()->getFile()
                  , $step->getDefinition()->getLine()
                );
            } else {
                $this->write();
            }

            // print step arguments
            if ($step->hasArguments()) {
                foreach ($step->getArguments() as $argument) {
                    if ($argument instanceof PyStringNode) {
                        $this->write($this->getPyString($argument, 6), $step->getResult());
                    } elseif ($argument instanceof TableNode) {
                        $this->write($this->getTableString($argument, 6), $step->getResult());
                    }
                }
            }

            // Print step exception
            if (null !== $step->getException()) {
                if ($this->verbose) {
                    $error = (string) $step->getException();
                } else {
                    $error = $step->getException()->getMessage();
                }
                $this->write(
                    '      ' . strtr($error, array("\n" => "\n      ")), $step->getResult()
                );
            }
        }
    }

    /**
     * Recalculates max descriptions size for section elements
     *
     * @param   Section $scenario   element for calculations
     * 
     * @return  integer             description length
     */
    protected function recalcMaxDescriptionLength(SectionNode $scenario)
    {
        $max    = $this->maxDescriptionLength;
        $type   = '';

        if ($scenario instanceof OutlineNode) {
            $type = $scenario->getI18n()->__('scenario-outline', 'Scenario Outline') . ':';
        } else if ($scenario instanceof ScenarioNode) {    
            $type = $scenario->getI18n()->__('scenario', 'Scenario') . ':';
        } else if ($scenario instanceof BackgroundNode) {
            $type = $scenario->getI18n()->__('background', 'Background') . ':';
        }
        $scenarioDescription = $scenario->getTitle() ? $type . ' ' . $scenario->getTitle() : $type;

        if (($tmp = mb_strlen($scenarioDescription) + 2) > $max) {
            $max = $tmp;
        }

        foreach ($scenario->getSteps() as $step) {
            $stepDescription = $step->getType() . ' ' . $step->getCleanText();
            if (($tmp = mb_strlen($stepDescription) + 4) > $max) {
                $max = $tmp;
            }
        }

        $this->maxDescriptionLength = $max;
    }

    /**
     * Returns formatted tag string, prepared for console output
     *
     * @param   Section $section    section instance
     * 
     * @return  string
     */
    protected function getTagsString(SectionNode $section)
    {
        $tags = array();
        foreach ($section->getTags() as $tag) {
            $tags[] = '@' . $tag;
        }

        return implode(' ', $tags);
    }

    /**
     * Returns formatted PyString, prepared for console output
     *
     * @param   PyString    $pystring   PyString instance
     * @param   integer     $indent     indentation spaces count
     * 
     * @return  string
     */
    protected function getPyString(PyStringNode $pystring, $indent = 6)
    {
        return strtr(
            sprintf("%s\"\"\"\n%s\n\"\"\"", str_repeat(' ', $indent), (string) $pystring),
            array("\n" => "\n" . str_repeat(' ', $indent))
        );
    }

    /**
     * Returns formatted Table, prepared for console output
     *
     * @param   Table       $table      Table instance
     * @param   string      $indent     indentation spaces count
     * 
     * @return  string
     */
    protected function getTableString(TableNode $table, $indent = 6)
    {
        return strtr(
            sprintf(str_repeat(' ', $indent).'%s', $table),
            array("\n" => "\n".str_repeat(' ', $indent))
        );
    }
}

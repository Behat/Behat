<?php

namespace Everzet\Behat\Formatter;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Gherkin\Element\SectionElement;
use Everzet\Gherkin\Element\ScenarioOutlineElement;
use Everzet\Gherkin\Element\ScenarioElement;
use Everzet\Gherkin\Element\BackgroundElement;
use Everzet\Gherkin\Element\Inline\PyStringElement;
use Everzet\Gherkin\Element\Inline\TableElement;

use Everzet\Behat\Runner\ScenarioOutlineRunner;
use Everzet\Behat\Runner\ScenarioRunner;
use Everzet\Behat\Runner\BackgroundRunner;

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
 * @package     Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PrettyFormatter implements FormatterInterface
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
        $this->output       = $container->getParameter('formatter.output');
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
        $dispatcher->connect('feature.test.before',         array($this, 'printFeatureHeader'));

        $dispatcher->connect('scenario_outline.test.before',array($this, 'printOutlineHeader'));
        $dispatcher->connect('scenario_outline.test.after', array($this, 'printOutlineFooter'));

        $dispatcher->connect('scenario.test.before',        array($this, 'printScenarioHeader'));
        $dispatcher->connect('scenario.test.after',         array($this, 'printScenarioFooter'));

        $dispatcher->connect('background.test.before',      array($this, 'printBackgroundHeader'));
        $dispatcher->connect('background.test.after',       array($this, 'printBackgroundFooter'));

        $dispatcher->connect('step.test.after',             array($this, 'printStep'));
        $dispatcher->connect('step.skip.after',             array($this, 'printStep'));

        $dispatcher->connect('features.test.after',         array($this, 'printStatistics'));
        $dispatcher->connect('features.test.after',         array($this, 'printSnippets'));
    }

    /**
     * Listens to `feature.pre_test` event & prints feature header (title & description)
     *
     * @param   Event   $event  notified event
     */
    public function printFeatureHeader(Event $event)
    {
        $runner     = $event->getSubject();
        $feature    = $runner->getFeature();

        // Print tags if had ones
        if ($feature->hasTags()) {
            $this->output->writeln(sprintf("<tag>%s</tag>", $this->getTagsString($feature)));
        }

        // Print feature title
        $this->output->writeln(sprintf("%s: %s",
            $feature->getI18n()->__('feature', 'Feature'),
            $feature->getTitle()
        ));

        // Print feature description
        foreach ($feature->getDescription() as $description) {
            $this->output->writeln(sprintf('  %s', $description));
        }
        $this->output->writeln('');

        // Run fake background to test if it runs without errors & prints it output
        if ($feature->hasBackground()) {
            $runner = new BackgroundRunner(
                $feature->getBackground()
              , $this->container->getEnvironmentService()
              , $this->container
              , null
            );
            $runner->run();
        }
    }

    /**
      * Listens to `scenario_outline.pre_test` event & prints outline header (title & description)
      *
      * @param   Event   $event  notified event
      */
    public function printOutlineHeader(Event $event)
    {
        $runner     = $event->getSubject();
        $outline    = $runner->getScenarioOutline();

        // Recalc maximum description length (for filepath-like comments)
        $this->recalcMaxDescriptionLength($outline);

        // Print tags if had ones
        if ($outline->hasTags()) {
            $this->output->writeln(sprintf("<tag>%s</tag>", $this->getTagsString($outline)));
        }

        // Print outline description
        $description = sprintf("  %s:%s",
            $outline->getI18n()->__('scenario-outline', 'Scenario Outline'),
            $outline->getTitle() ? ' ' . $outline->getTitle() : ''
        );
        $this->output->write($description);

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
        $this->output->writeln('');
    }

    /**
      * Listens to `scenario.pre_test` event & prints scenario header (title & description)
      *
      * @param   Event   $event  notified event
      */
    public function printScenarioHeader(Event $event)
    {
        $runner     = $event->getSubject();
        $scenario   = $runner->getScenario();

        // Recalc maximum description length (for filepath-like comments)
        $this->recalcMaxDescriptionLength($scenario);

        if (!$runner->isInOutline()) {
            // Print tags if had ones
            if ($scenario->hasTags()) {
                $this->output->writeln(sprintf("  <tag>%s</tag>",
                    $this->getTagsString($scenario)
                ));
            }

            // Print scenario description
            $description = sprintf("  %s:%s",
                $scenario->getI18n()->__('scenario', 'Scenario'),
                $scenario->getTitle() ? ' ' . $scenario->getTitle() : ''
            );
            $this->output->write($description);

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
        $runner     = $event->getSubject();
        $scenario   = $runner->getScenario();

        if (!$runner->isInOutline()) {
            $this->output->writeln('');
        } else {
            $outlineRunner  = $runner->getParentRunner();
            $outline        = $outlineRunner->getScenarioOutline();
            $examples       = $outline->getExamples()->getTable();

            // Print outline description with steps & examples after first scenario in batch runned
            if (0 === $outlineRunner->key()) {

                // Print outline steps
                foreach ($runner->getChildRunners() as $stepRunner) {
                    $step = $stepRunner->getStep();

                    // Print step description
                    $description = sprintf('    %s %s', $step->getType(), $step->getCleanText());
                    $this->output->write(sprintf("\033[36m%s\033[0m", $description), false, 1);

                    // Print definition/element path
                    if (null !== $stepRunner->getDefinition()) {
                        $this->printLineSourceComment(
                            mb_strlen($description)
                          , $stepRunner->getDefinition()->getFile()
                          , $stepRunner->getDefinition()->getLine()
                        );
                    } else {
                        $this->output->writeln('');
                    }
                }

                // Print outline examples title
                $this->output->writeln(sprintf("\n    %s:",
                    $outline->getI18n()->__('examples', 'Examples')
                ));

                // print outline examples header row
                $this->output->writeln(preg_replace(
                    '/|([^|]*)|/', '<skipped>$1</skipped>', '      ' . $examples->getKeysAsString()
                ));
            }

            // print current scenario results row
            $this->output->writeln(preg_replace(
                '/|([^|]*)|/'
              , sprintf('<%s>$1</%s>', $runner->getStatus(), $runner->getStatus())
              , '      ' . $examples->getRowAsString($outlineRunner->key())
            ));

            // Print errors
            foreach ($runner->getFailedStepRunners() as $stepRunner) {
                if ($this->verbose) {
                    $error = (string) $stepRunner->getException();
                } else {
                    $error = $stepRunner->getException()->getMessage();
                }
                $this->output->write(sprintf("        \033[31m%s\033[0m",
                    strtr($error, array("\n" => "\n      "))
                ), true, 1);
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
        $runner     = $event->getSubject();
        $background = $runner->getBackground();

        // Recalc maximum description length (for filepath-like comments)
        $this->recalcMaxDescriptionLength($background);

        if (null === $runner->getParentRunner()) {
            // Print description
            $description = sprintf("  %s:%s",
                $background->getI18n()->__('background', 'Background'),
                $background->getTitle() ? ' ' . $background->getTitle() : ''
            );
            $this->output->write($description);

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
        $runner = $event->getSubject();

        if (null === $runner->getParentRunner()) {
            $this->output->writeln('');
        }
    }

    /**
      * Listens to `step.post_test` event & prints step runner information
      *
      * @param   Event   $event  notified event
      */
    public function printStep(Event $event)
    {
        $runner = $event->getSubject();
        $step   = $runner->getStep();

        if (
            // Not in scenario background
            !(null !== $runner->getParentRunner() &&
              $runner->getParentRunner() instanceof BackgroundRunner &&
              null !== $runner->getParentRunner()->getParentRunner() &&
              $runner->getParentRunner()->getParentRunner() instanceof ScenarioRunner) &&

            // Not in outline
            !(null !== $runner->getParentRunner() &&
              null !== $runner->getParentRunner()->getParentRunner() &&
              $runner->getParentRunner()->getParentRunner() instanceof ScenarioOutlineRunner)
           ) {
            // Print step description
            $description = sprintf('    %s %s', $step->getType(), $step->getText());
            $this->output->write(sprintf('<%s>%s</%s>',
                $runner->getStatus(), $description, $runner->getStatus()
            ));

            // Print definition path if found one
            if (null !== $runner->getDefinition()) {
                $this->printLineSourceComment(
                    mb_strlen($description)
                  , $runner->getDefinition()->getFile()
                  , $runner->getDefinition()->getLine()
                );
            } else {
                $this->output->writeln('');
            }

            // print step arguments
            if ($step->hasArguments()) {
                foreach ($step->getArguments() as $argument) {
                    if ($argument instanceof PyStringElement) {
                        $this->output->write(sprintf("\033[%sm%s\033[0m",
                            $this->getStatusColorCode($runner->getStatus()),
                            $this->getPyString($argument, 6),
                            $this->getStatusColorCode($runner->getStatus())
                        ), true, 1);
                    } elseif ($argument instanceof TableElement) {
                        $this->output->write(sprintf("\033[%sm%s\033[0m",
                            $this->getStatusColorCode($runner->getStatus()),
                            $this->getTableString($argument, 6),
                            $this->getStatusColorCode($runner->getStatus())
                        ), true, 1);
                    }
                }
            }

            // Print step exception
            if (null !== $runner->getException()) {
                if ($this->verbose) {
                    $error = (string) $runner->getException();
                } else {
                    $error = $runner->getException()->getMessage();
                }
                $this->output->write(sprintf("      \033[%sm%s\033[0m"
                  , 'failed' === $runner->getStatus() ? '31' : '33'
                  , strtr($error, array("\n" => "\n      "))
                ), true, 1);
            }
        }
    }

    /**
      * Listens to `suite.post_test` event & prints all tests statistics
      *
      * @param   Event   $event  notified event
      */
    public function printStatistics(Event $event)
    {
        $runner = $event->getSubject();

        $stepsCount             = $runner->getStepsCount();
        $scenariosCount         = $runner->getScenariosCount();
        $scenariosStatusesCount = $runner->getScenariosStatusesCount();
        $stepsStatusesCount     = $runner->getStepsStatusesCount();

        $statuses = array();
        foreach ($scenariosStatusesCount as $status => $count) {
            $statuses[] = sprintf('<%s>%s %s</%s>', $status, $count, $status, $status);
        }
        $this->output->writeln(sprintf('%d scenarios %s',
            $scenariosCount
          , count($statuses) ? sprintf('(%s)', implode(', ', $statuses)) : ''
        ));

        $statuses = array();
        foreach ($stepsStatusesCount as $status => $count) {
            $statuses[] = sprintf('<%s>%s %s</%s>', $status, $count, $status, $status);
        }
        $this->output->writeln(sprintf('%d steps %s',
            $stepsCount
          , count($statuses) ? sprintf('(%s)', implode(', ', $statuses)) : ''
        ));

        $this->output->writeln(sprintf("%.3fs", $runner->getTime()));
    }

    /**
      * Listens to `suite.post_test` event & prints step snippets for undefined steps
      *
      * @param   Event   $event  notified event
      */
    public function printSnippets(Event $event)
    {
        $runner             = $event->getSubject();
        $stepsStatusesCount = $runner->getStepsStatusesCount();

        if (isset($stepsStatusesCount['undefined'])) {
            $this->output->writeln("\n<undefined>" .
                "You can implement step definitions for undefined steps with these snippets:" .
            "</undefined>\n");

            foreach ($runner->getDefinitionSnippets() as $key => $snippet) {
                $this->output->writeln('<undefined>' . $snippet . "</undefined>\n");
            }
        }
    }

    /**
     * Prints comment line with source info
     *
     * @param   integer $lineLength     current line length
     * @param   string  $file           source file
     * @param   integer $line           source line
     */
    protected function printLineSourceComment($lineLength, $file, $line)
    {
        $indent = $lineLength > $this->maxDescriptionLength
            ? 0
            : $this->maxDescriptionLength - $lineLength;
        $file   = preg_replace('/.*\/features\//', 'features/', $file);

        $this->output->writeln(sprintf("%s <comment># %s:%d</comment>",
            str_repeat(' ', $indent), $file, $line
        ));
    }

    /**
     * Recalculates max descriptions size for section elements
     *
     * @param   Section $scenario   element for calculations
     * 
     * @return  integer             description length
     */
    protected function recalcMaxDescriptionLength(SectionElement $scenario)
    {
        $max    = $this->maxDescriptionLength;
        $type   = '';

        if ($scenario instanceof ScenarioOutlineElement) {
            $type = $scenario->getI18n()->__('scenario-outline', 'Scenario Outline') . ':';
        } else if ($scenario instanceof ScenarioElement) {    
            $type = $scenario->getI18n()->__('scenario', 'Scenario') . ':';
        } else if ($scenario instanceof BackgroundElement) {
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
     * Returns color code for custom status
     *
     * @param   string  $status status (passed/skipped/failed etc.)
     * 
     * @return  integer         console color code
     */
    protected function getStatusColorCode($status)
    {
        $colorCode = 32;
        switch ($status) {
            case 'failed':
                $colorCode = 31;
                break;
            case 'undefined':
            case 'pending':
                $colorCode = 33;
                break;
            case 'skipped':
            case 'tag':
                $colorCode = 36;
                break;
            case 'comment':
                $colorCode = 30;
                break;
        }

        return $colorCode;
    }

    /**
     * Returns formatted tag string, prepared for console output
     *
     * @param   Section $section    section instance
     * 
     * @return  string
     */
    protected function getTagsString(SectionElement $section)
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
    protected function getPyString(PyStringElement $pystring, $indent = 6)
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
    protected function getTableString(TableElement $table, $indent = 6)
    {
        return strtr(
            sprintf(str_repeat(' ', $indent).'%s', $table),
            array("\n" => "\n".str_repeat(' ', $indent))
        );
    }
}

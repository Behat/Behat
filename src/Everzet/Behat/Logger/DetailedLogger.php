<?php

namespace Everzet\Behat\Logger;

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

class DetailedLogger implements LoggerInterface
{
    protected $container;
    protected $output;
    protected $verbose;
    protected $maxDescriptionLength = 0;

    public function __construct(Container $container)
    {
        $this->container    = $container;
        $this->output       = $container->getParameter('logger.output');
        $this->verbose      = $container->getParameter('logger.verbose');

        $this->output->setStyle('failed',      array('fg' => 'red'));
        $this->output->setStyle('undefined',   array('fg' => 'yellow'));
        $this->output->setStyle('pending',     array('fg' => 'yellow'));
        $this->output->setStyle('passed',      array('fg' => 'green'));
        $this->output->setStyle('skipped',     array('fg' => 'cyan'));
        $this->output->setStyle('comment',     array('fg' => 'black'));
        $this->output->setStyle('tag',         array('fg' => 'cyan'));
    }

    public function registerListeners(EventDispatcher $dispatcher)
    {
        $dispatcher->connect('feature.pre_test',            array($this, 'beforeFeature'));

        $dispatcher->connect('scenario_outline.pre_test',   array($this, 'beforeScenarioOutline'));
        $dispatcher->connect('scenario_outline.post_test',  array($this, 'afterScenarioOutline'));

        $dispatcher->connect('scenario.pre_test',           array($this, 'beforeScenario'));
        $dispatcher->connect('scenario.post_test',          array($this, 'afterScenario'));

        $dispatcher->connect('background.pre_test',         array($this, 'beforeBackground'));
        $dispatcher->connect('background.post_test',        array($this, 'afterBackground'));

        $dispatcher->connect('step.post_test',              array($this, 'afterStep'));
    }

    public function beforeFeature(Event $event)
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
              , $this->container->getStepsLoaderService()
              , $this->container
              , $this
            );
            $runner->run();
        }
    }

    public function beforeScenarioOutline(Event $event)
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
        $this->output->writeln($this->getLineSourceComment(
            mb_strlen($description)
          , $outline->getFile()
          , $outline->getLine()
        ));
    }

    public function afterScenarioOutline(Event $event)
    {
        $this->output->writeln('');
    }

    public function beforeScenario(Event $event)
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
            $this->output->writeln($this->getLineSourceComment(
                mb_strlen($description)
              , $scenario->getFile()
              , $scenario->getLine()
            ));
        }
    }

    public function afterScenario(Event $event)
    {
        $runner     = $event->getSubject();
        $scenario   = $runner->getScenario();

        if (!$runner->isInOutline()) {
            $this->output->writeln('');
        } else {
            $outlineRunner  = $runner->getCaller();
            $outline        = $outlineRunner->getScenarioOutline();
            $examples       = $outline->getExamples()->getTable();

            // Print outline description with steps & examples after first scenario in batch runned
            if (0 === $outlineRunner->key()) {

                // Print outline steps
                foreach ($runner->getStepRunners() as $stepRunner) {
                    $step = $stepRunner->getStep();

                    // Print step description
                    $description = sprintf('    %s %s', $step->getType(), $step->getCleanText());
                    $this->output->write(sprintf("\033[36m%s\033[0m", $description), false, 1);

                    // Print definition/element path
                    if (null !== $stepRunner->getDefinition()) {
                        $this->output->writeln($this->getLineSourceComment(
                            mb_strlen($description)
                          , $stepRunner->getDefinition()->getFile()
                          , $stepRunner->getDefinition()->getLine()
                        ));
                    } else {
                        $this->output->writeln('');
                    }
                }

                // Print outline examples title
                $this->output->writeln(sprintf("\n    %s:",
                    $outline->getI18n()->__('examples', 'Examples')
                ));

                // Draw outline examples header row
                $this->output->writeln(preg_replace(
                    '/|([^|]*)|/', '<skipped>$1</skipped>', '      ' . $examples->getKeysAsString()
                ));
            }

            // Draw current scenario results row
            $this->output->writeln(preg_replace(
                '/|([^|]*)|/'
              , sprintf('<%s>$1</%s>', $runner->getStatus(), $runner->getStatus())
              , '      ' . $examples->getRowAsString($outlineRunner->key())
            ));

            // Print errors
            foreach ($runner->getExceptions() as $exception) {
                if ($this->verbose) {
                    $error = (string) $exception;
                } else {
                    $error = $exception->getMessage();
                }
                $this->output->writeln(sprintf("        <failed>%s</failed>",
                    strtr($error, array(
                        "\n"    =>  "\n      "
                      , "<"     =>  "["
                      , ">"     =>  "]"
                    ))
                ));
            }
        }
    }

    public function beforeBackground(Event $event)
    {
        $runner     = $event->getSubject();
        $background = $runner->getBackground();

        // Recalc maximum description length (for filepath-like comments)
        $this->recalcMaxDescriptionLength($background);

        if (null === $runner->getCaller()) {
            // Print description
            $description = sprintf("  %s:%s",
                $background->getI18n()->__('background', 'Background'),
                $background->getTitle() ? ' ' . $background->getTitle() : ''
            );
            $this->output->write($description);

            // Print element path & line
            $this->output->writeln($this->getLineSourceComment(
                mb_strlen($description)
              , $background->getFile()
              , $background->getLine()
            ));
        }
    }

    public function afterBackground(Event $event)
    {
        $runner = $event->getSubject();

        if (null === $runner->getCaller()) {
            $this->output->writeln('');
        }
    }

    public function afterStep(Event $event)
    {
        $runner = $event->getSubject();
        $step   = $runner->getStep();

        if (
            // Not in scenario background
            !(null !== $runner->getCaller() &&
              $runner->getCaller() instanceof BackgroundRunner &&
              null !== $runner->getCaller()->getCaller() &&
              $runner->getCaller()->getCaller() instanceof ScenarioRunner) &&

            // Not in outline
            !(null !== $runner->getCaller() &&
              null !== $runner->getCaller()->getCaller() &&
              $runner->getCaller()->getCaller() instanceof ScenarioOutlineRunner)
           ) {
            // Print step description
            $description = sprintf('    %s %s', $step->getType(), $step->getText());
            $this->output->write(sprintf('<%s>%s</%s>',
                $runner->getStatus(), $description, $runner->getStatus()
            ));

            // Print definition path if found one
            if (null !== $runner->getDefinition()) {
                $this->output->writeln($this->getLineSourceComment(
                    mb_strlen($description)
                  , $runner->getDefinition()->getFile()
                  , $runner->getDefinition()->getLine()
                ));
            } else {
                $this->output->writeln('');
            }

            // Draw step arguments
            if ($step->hasArguments()) {
                foreach ($step->getArguments() as $argument) {
                    if ($argument instanceof PyStringElement) {
                        $this->output->writeln(sprintf("<%s>%s</%s>",
                            $runner->getStatus(),
                            $this->getPyString($argument, 6),
                            $runner->getStatus()
                        ));
                    } elseif ($argument instanceof TableElement) {
                        $this->output->writeln(sprintf("<%s>%s</%s>",
                            $runner->getStatus(),
                            $this->getTableString($argument, 6),
                            $runner->getStatus()
                        ));
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
                $this->output->writeln(sprintf("      <failed>%s</failed>",
                    strtr($error, array(
                        "\n"    =>  "\n      ",
                        "<"     =>  "[",
                        ">"     =>  "]"
                    ))
                ));
            }
        }
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

    /**
     * Returns comment line with source info
     *
     * @param   integer $lineLength     current line length
     * @param   integer $stepsMaxLength line max length
     * @param   string  $file           definition filename
     * @param   integer $line           definition line
     */
    protected function getLineSourceComment($lineLength, $file, $line)
    {
        $indent = $lineLength > $this->maxDescriptionLength
            ? 0
            : $this->maxDescriptionLength - $lineLength;
        $file   = preg_replace('/.*\/features\//', '', $file);

        return sprintf("%s <comment># %s:%d</comment>",
            str_repeat(' ', $indent), $file, $line
        );
    }

    /**
     * Calculates max descriptions size for scenario/background
     *
     * @param   Section $scenario   scenario for calculations
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
}

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

class PrettyFormatter implements FormatterInterface
{
    protected $container;
    protected $output;
    protected $verbose;
    protected $maxDescriptionLength = 0;

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

    public function registerListeners(EventDispatcher $dispatcher)
    {
        $dispatcher->connect('feature.pre_test',            array($this, 'printFeatureHeader'));

        $dispatcher->connect('scenario_outline.pre_test',   array($this, 'printOutlineHeader'));
        $dispatcher->connect('scenario_outline.post_test',  array($this, 'printOutlineFooter'));

        $dispatcher->connect('scenario.pre_test',           array($this, 'printScenarioHeader'));
        $dispatcher->connect('scenario.post_test',          array($this, 'printScenarioFooter'));

        $dispatcher->connect('background.pre_test',         array($this, 'printBackgroundHeader'));
        $dispatcher->connect('background.post_test',        array($this, 'printBackgroundFooter'));

        $dispatcher->connect('step.post_test',              array($this, 'printStep'));
        $dispatcher->connect('step.post_skip',              array($this, 'printStep'));

        $dispatcher->connect('suite.post_test',             array($this, 'printStatistics'));
        $dispatcher->connect('suite.post_test',             array($this, 'printSnippets'));
    }

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
              , $this->container->getStepsLoaderService()
              , $this->container
              , null
            );
            $runner->run();
        }
    }

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

    public function printOutlineFooter(Event $event)
    {
        $this->output->writeln('');
    }

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

    public function printBackgroundFooter(Event $event)
    {
        $runner = $event->getSubject();

        if (null === $runner->getParentRunner()) {
            $this->output->writeln('');
        }
    }

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
        $this->output->writeln(sprintf('%d scenarios (%s)',
            $scenariosCount, implode(', ', $statuses)
        ));

        $statuses = array();
        foreach ($stepsStatusesCount as $status => $count) {
            $statuses[] = sprintf('<%s>%s %s</%s>', $status, $count, $status, $status);
        }
        $this->output->writeln(sprintf('%d steps (%s)',
            $stepsCount, implode(', ', $statuses)
        ));

        $this->output->writeln(sprintf("%.3fs", $runner->getTime()));
    }

    public function printSnippets(Event $event)
    {
        $runner             = $event->getSubject();
        $stepsStatusesCount = $runner->getStepsStatusesCount();

        if (isset($stepsStatusesCount['undefined'])) {
            $this->output->writeln("\n<undefined>" .
                "You can implement step definitions for undefined steps with these snippets:" .
            "</undefined>\n");

            foreach ($runner->getDefinitionSnippets() as $key => $snippet) {
                $this->output->writeln(
                    '<undefined>' . sprintf($snippet[1], $snippet[0]) . "</undefined>\n"
                );
            }
        }
    }

    /**
     * Returns comment line with source info
     *
     * @param   integer $lineLength     current line length
     * @param   integer $stepsMaxLength line max length
     * @param   string  $file           definition filename
     * @param   integer $line           definition line
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

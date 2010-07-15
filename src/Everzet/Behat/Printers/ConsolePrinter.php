<?php

namespace Everzet\Behat\Printers;

use \Everzet\Gherkin\I18n;
use \Everzet\Gherkin\Structures\Section;
use \Everzet\Gherkin\Structures\Feature;
use \Everzet\Gherkin\Structures\Step;
use \Everzet\Gherkin\Structures\Scenario\Background;
use \Everzet\Gherkin\Structures\Scenario\ScenarioOutline;
use \Everzet\Gherkin\Structures\Scenario\Scenario;
use \Everzet\Gherkin\Structures\Inline\PyString;
use \Everzet\Gherkin\Structures\Inline\Table;
use \Everzet\Gherkin\Structures\Inline\Examples;
use \Everzet\Behat\Stats\TestStats;
use \Everzet\Behat\Definitions\StepsContainer;
use \Everzet\Behat\Printers\Printer;

use \Symfony\Components\Console\Output\OutputInterface;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * ConsolePrinter implements Printer interface with Symfony's OutputInterface methods
 *
 * @package     behat
 * @subpackage  Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ConsolePrinter implements Printer
{
    protected $i18n;
    protected $verbose;
    protected $output;
    protected $basePath;
    protected $stepsMaxLength = 0;
    protected $baseIndent = 0;

    /**
     * Constructs new printer
     *
     * @param   OutputInterface $output     Symfony's OutputInterface object
     * @param   I18n            $i18n       I18n instance
     * @param   string          $basePath   features base path
     * @param   boolean         $verbose    is output verbose
     */
    public function __construct(OutputInterface $output, I18n $i18n, $basePath, $verbose = false)
    {
        $this->i18n = $i18n;
        $this->output = $output;
        $this->basePath = $basePath;
        $this->verbose = $verbose;
        $this->setColors();
    }

    /**
     * Sets console colors
     */
    protected function setColors()
    {
        $this->output->setStyle('failed',      array('fg' => 'red'));
        $this->output->setStyle('undefined',   array('fg' => 'yellow'));
        $this->output->setStyle('pending',     array('fg' => 'yellow'));
        $this->output->setStyle('passed',      array('fg' => 'green'));
        $this->output->setStyle('skipped',     array('fg' => 'cyan'));
        $this->output->setStyle('comment',     array('fg' => 'black'));
        $this->output->setStyle('tag',         array('fg' => 'cyan'));
    }

    /**
     * Left trims base path out of message
     *
     * @param   string  $message    message to be trimmed
     * 
     * @return  string              trimmed message
     */
    protected function ltrimPaths($message)
    {
        return strtr($message, array($this->basePath . '/' => ''));
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logFeatureBegin(Feature $feature, $file)
    {
        if ($feature->hasTags()) {
            $this->output->writeln(sprintf("<tag>%s</tag>", $this->getTagsString($feature)));
        }
        $this->output->writeln(sprintf("%s: %s  <comment>#%s</comment>",
            $this->i18n->__('feature', 'Feature'),
            $feature->getTitle(), $this->ltrimPaths(realpath($file))
        ));
        foreach ($feature->getDescription() as $description) {
            $this->output->writeln(sprintf('  %s', $description));
        }
        $this->output->writeln('');
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logFeatureEnd(Feature $feature, $file)
    {
        $this->output->writeln('');
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logBackgroundBegin(Background $background)
    {
        $space = str_repeat(' ', 2 + $this->baseIndent);

        $this->output->writeln(sprintf("%s<passed>%s: %s</passed>",
            $space, $this->i18n->__('background', 'Background'), $background->getTitle()
        ));

        // Calculate max step description length
        $this->stepsMaxLength = $this->getStepsMaxLength($background);
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logBackgroundEnd(Background $background)
    {
        $this->output->writeln('');
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logScenarioOutlineBegin(ScenarioOutline $scenario)
    {
        if ($scenario->hasTags()) {
            $this->output->writeln(sprintf("  <tag>%s</tag>", $this->getTagsString($scenario)));
        }
        $this->output->writeln(sprintf("  <passed>%s: %s</passed>",
            $this->i18n->__('scenario-outline', 'Scenario Outline'),
            $scenario->getTitle()
        ));
        $this->baseIndent += 2;
        $this->output->writeln('');

        // Calculate max step description length
        $this->stepsMaxLength = $this->getStepsMaxLength($scenario);
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logScenarioOutlineEnd(ScenarioOutline $scenario)
    {
        $this->baseIndent -= 2;
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logScenarioBegin(Scenario $scenario)
    {
        $space = str_repeat(' ', 2 + $this->baseIndent);

        if ($scenario->hasTags()) {
            $this->output->writeln(sprintf("%s<tag>%s</tag>",
                $space, $this->getTagsString($scenario)
            ));
        }
        $this->output->writeln(sprintf("%s<passed>%s: %s</passed>",
            $space, $this->i18n->__('scenario', 'Scenario'), $scenario->getTitle()
        ));

        // Calculate max step description length
        $this->stepsMaxLength = $this->getStepsMaxLength($scenario);
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logScenarioEnd(Scenario $scenario)
    {
        $this->output->writeln('');
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logStep($code, $type, $text, $file = null,
                            $line = null, array $args = array(), \Exception $e = null)
    {
        $space = str_repeat(' ', 4 + $this->baseIndent);
        $errorsSpace = str_repeat(' ', 6 + $this->baseIndent);

        $description = sprintf('%s %s', $type, $text);
        $status = sprintf('%s<%s>%s</%s>', $space, $code, $description, $code);

        // Calculate pad length (between comment & step description)
        $length  = $this->stepsMaxLength;
        // Code tags
        $length += 5 + (strlen($code) * 2);
        // Indentation
        $length += 4 + $this->baseIndent;
        // Space between
        $length += 2;

        // Pad step description right
        $status = str_pad($status, $length);

        if (null !== $file && null !== $line) {
            $status .= sprintf('<comment>%s:%d</comment>',
                $this->ltrimPaths(realpath($file)), $line
            );
        }
        $this->output->writeln($status);

        $this->logStepArguments($code, $args);

        if (null !== $e) {
            $error = $this->verbose ? $e->__toString() : $e->getMessage();
            $this->output->writeln(sprintf("%s<failed>%s</failed>",
                $errorsSpace,
                strtr($this->ltrimPaths($error), array(
                    "\n"    =>  "\n" . $errorsSpace,
                    "<"     =>  "[",
                    ">"     =>  "]"
                ))
            ));
        }
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logStepArguments($code, array $args)
    {
        foreach ($args as $argument) {
            if ($argument instanceof PyString) {
                $this->output->writeln(sprintf("<%s>%s</%s>",
                    $code, $this->getPyString($argument, 6 + $this->baseIndent), $code
                ));
            } elseif ($argument instanceof Table) {
                $this->output->writeln(sprintf("<%s>%s</%s>",
                    $code, $this->getTable($argument, 6 + $this->baseIndent), $code
                ));
            }
        }
    }

    /**
     * Calculates max step description size for scenario/background
     *
     * @param   Section $scenario   scenario for calculations
     * 
     * @return  integer             description length
     */
    protected function getStepsMaxLength(Section $scenario)
    {
        $max = 0;

        foreach ($scenario->getSteps() as $step) {
            $stepDefinition = $step->getType() . ' ' . $step->getText();
            if (($tmp = strlen($stepDefinition)) > $max) {
                $max = $tmp;
            }
        }

        return $max;
    }

    /**
     * Returns formatted tag string, prepared for console output
     *
     * @param   Section $section    section instance
     * 
     * @return  string
     */
    protected function getTagsString(Section $section)
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
    protected function getPyString(PyString $pystring, $indent = 6)
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
    protected function getTable(Table $table, $indent = 6)
    {
        return strtr(
            sprintf(str_repeat(' ', $indent).'%s', $table),
            array("\n" => "\n".str_repeat(' ', $indent))
        );
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logStats(TestStats $stats, StepsContainer $steps)
    {
        $details = array();
        foreach ($stats->getStatisticStatusTypes() as $type) {
            if ($stats->getScenarioStatusCount($type)) {
                $details[] = sprintf('<%s>%d %s</%s>',
                    $type, $stats->getScenarioStatusCount($type), $type, $type
                );
            }
        }
        $this->output->writeln(sprintf('%d scenarios (%s)',
            $stats->getScenariosCount(), implode(', ', $details)
        ));

        $details = array();
        foreach ($stats->getStatisticStatusTypes() as $type) {
            if ($stats->getStepStatusCount($type)) {
                $details[] = sprintf('<%s>%d %s</%s>',
                    $type, $stats->getStepStatusCount($type), $type, $type
                );
            }
        }
        $this->output->writeln(sprintf('%d steps (%s)',
            $stats->getStepsCount(), implode(', ', $details)
        ));

        if ($stats->getStepStatusCount('undefined')) {
            $this->output->writeln(sprintf(
                "\n<undefined>You can implement step definitions for undefined steps with these snippets:</undefined>%s\n",
                $steps->getUndefinedStepsSnippets()
            ));
        } else {
            $this->output->writeln('');
        }
    }
}

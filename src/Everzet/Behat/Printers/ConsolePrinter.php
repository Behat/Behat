<?php

namespace Everzet\Behat\Printers;

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
    protected $verbose;
    protected $output;
    protected $basePath;

    /**
     * Constructs new printer
     *
     * @param   OutputInterface $output     Symfony's OutputInterface object
     * @param   string          $basePath   features base path
     */
    public function __construct(OutputInterface $output, $basePath, $verbose = false)
    {
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
    public function logFeature(Feature $feature, $file)
    {
        $this->output->writeln(sprintf("Feature: %s  <comment>#%s</comment>",
            $feature->getTitle(), $this->ltrimPaths(realpath($file))
        ));
        foreach ($feature->getDescription() as $description) {
            $this->output->writeln(sprintf('  %s', $description));
        }
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logBackground(Background $background)
    {
        $this->output->writeln(sprintf("\n  <passed>Background: %s</passed>",
            $background->getTitle()
        ));
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logScenarioOutline(ScenarioOutline $scenario)
    {
        $this->output->writeln(sprintf("\n  <passed>Scenario Outline: %s</passed>",
            $scenario->getTitle()
        ));
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logScenario(Scenario $scenario)
    {
        $this->output->writeln(sprintf("\n  <passed>Scenario: %s</passed>",
            $scenario->getTitle()
        ));
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logStep($code, $type, $text, $file = null,
                            $line = null, array $args = array(), \Exception $e = null)
    {
        $description = sprintf('%s %s', $type, $text);
        $status = sprintf('    <%s>%s</%s>', $code, $description, $code);
        $status = str_pad($status, 60 + (strlen($code) * 2));

        if (null !== $file && null !== $line) {
            $status .= sprintf('<comment>%s:%d</comment>',
                $this->ltrimPaths(realpath($file)), $line
            );
        }
        $this->output->writeln($status);

        $this->logStepArguments($code, $args);

        if (null !== $e) {
            $error = $this->verbose ? $e->__toString() : $e->getMessage();
            $this->output->writeln(sprintf("        <failed>%s</failed>",
                strtr($this->ltrimPaths($error), array(
                    "\n"    =>  "\n        ",
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
                    $code, $this->getPyString($argument, 6), $code
                ));
            } elseif ($argument instanceof Table) {
                $this->output->writeln(sprintf("<%s>%s</%s>",
                    $code, $this->getTable($argument, 6), $code
                ));
            }
        }
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

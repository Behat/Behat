<?php

namespace Everzet\Behat\Printers;

use \Everzet\Gherkin\Feature;
use \Everzet\Gherkin\Background;
use \Everzet\Gherkin\ScenarioOutline;
use \Everzet\Gherkin\Scenario;
use \Everzet\Behat\Stats\TestStats;
use \Everzet\Behat\Definitions\StepsContainer;

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
        $this->output->writeln(sprintf("\n    <passed>Background: %s</passed>",
            $background->getTitle()
        ));
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logScenarioOutline(ScenarioOutline $scenario)
    {
        $this->output->writeln(sprintf("\n    <passed>Scenario Outline: %s</passed>",
            $scenario->getTitle()
        ));
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logScenario(Scenario $scenario)
    {
        $this->output->writeln(sprintf("\n    <passed>Scenario: %s</passed>",
            $scenario->getTitle()
        ));
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logStep($code, $type, $text, $file = null,
                            $line = null, \Exception $e = null)
    {
        $status = sprintf('      <%s>%s</%s>', $code, $type . ' ' . $text, $code);
        $status = str_pad($status, 60 + (strlen($code) * 2));

        if (null !== $file && null !== $line) {
            $status .= sprintf('<comment>%s:%d</comment>',
                $this->ltrimPaths(realpath($file)), $line
            );
        }

        $this->output->writeln($status);

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
                "\n<undefined>You can implement step definitions for undefined steps with these snippets:%s</undefined>\n",
                $steps->getUndefinedStepsSnippets()
            ));
        } else {
            $this->output->writeln('');
        }
    }
}

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
    protected $file;
    protected $i18n;
    protected $verbose;
    protected $output;
    protected $basePath;
    protected $stepsMaxLength = 0;

    protected $inBackground = false;
    protected $hasBackground = false;

    protected $inOutline = false;
    protected $outlineScenarioNum = 0;
    protected $outlineScenarioStepResults = array();
    protected $outlineStepsInfo = array();
    protected $outlineFirstRun = true;

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
    public function setFile($file)
    {
        $this->file = realpath($file);
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logFeatureBegin(Feature $feature, $file)
    {
        if ($feature->hasTags()) {
            $this->output->writeln(sprintf("<tag>%s</tag>", $this->getTagsString($feature)));
        }
        $this->output->writeln(sprintf("%s: %s",
            $this->i18n->__('feature', 'Feature'),
            $feature->getTitle()
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
        $this->inBackground = true;
        if (!$this->hasBackground) {
            // Calculate max step description length
            $this->recalcStepsMaxLength($background);

            $description = sprintf("  %s:%s",
                $this->i18n->__('background', 'Background'),
                $background->getTitle() ? ' ' . $background->getTitle() : ''
            );
            $this->output->write($description);

            $this->logLineSourceComment(mb_strlen($description), $this->file, $background->getLine());
        }
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logBackgroundEnd(Background $background)
    {
        if (!$this->hasBackground) {
            $this->output->writeln('');
            $this->hasBackground = true;
        }
        $this->inBackground = false;
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logScenarioOutlineBegin(ScenarioOutline $scenario)
    {
        $this->inOutline = true;

        // Calculate max step description length
        $this->recalcStepsMaxLength($scenario);

        if ($scenario->hasTags()) {
            $this->output->writeln(sprintf("  <tag>%s</tag>", $this->getTagsString($scenario)));
        }

        $description = sprintf("  %s:%s",
            $this->i18n->__('scenario-outline', 'Scenario Outline'),
            $scenario->getTitle() ? ' ' . $scenario->getTitle() : ''
        );
        $this->output->write($description);
        $this->logLineSourceComment(mb_strlen($description), $this->file, $scenario->getLine());
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logScenarioOutlineEnd(ScenarioOutline $scenario)
    {
        $this->outlineScenarioNum = 0;
        $this->inOutline = false;
        $this->outlineFirstRun = true;
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logIntermediateOutlineScenario(Scenario $scenario)
    {
        if ($this->outlineFirstRun) {
            $this->outlineFirstRun = false;

            // Print steps
            foreach ($scenario->getSteps() as $i => $step) {
                $description = sprintf('    %s %s', $step->getType(), $step->getText());
                $this->output->write(sprintf("\033[36m%s\033[0m", $description), false, 1);
                $this->logLineSourceComment(
                    mb_strlen($description),
                    $this->outlineStepsInfo[$i][0],
                    $this->outlineStepsInfo[$i][1]
                );
            }

            // Print Examples:
            $this->output->writeln(sprintf("\n    %s:", $this->i18n->__('examples', 'Examples')));

            // Print table header
            $this->output->writeln(preg_replace(
                '/|([^|]*)|/',
                '<skipped>$1</skipped>',
                '      ' . $scenario->getExamples()->getTable()->getKeysAsString()
            ));
        }

        $examplesTable = $scenario->getExamples()->getTable();
        $status = 'passed';
        $e = null;

        foreach ($this->outlineScenarioStepResults as $stepResult) {
            if ('failed' != $status) {
                $status = $stepResult[0];
            }
            if ('failed' == $stepResult[0]) {
                $e = $stepResult[1];
            }
        }

        $this->output->writeln(preg_replace(
            '/|([^|]*)|/',
            sprintf('<%s>$1</%s>', $status, $status),
            '      ' . $examplesTable->getRowAsString($this->outlineScenarioNum)
        ));

        if (null !== $e) {
            $this->logStepError($e);
        }

        $this->outlineScenarioNum++;
        $this->outlineStepsInfo = array();
        $this->outlineScenarioStepResults = array();
    }

    /**
     * @see \Everzet\Behat\Printers\Printer
     */
    public function logScenarioBegin(Scenario $scenario)
    {
        // Calculate max step description length
        $this->recalcStepsMaxLength($scenario);

        if ($scenario->hasTags()) {
            $this->output->writeln(sprintf("  <tag>%s</tag>",
                $this->getTagsString($scenario)
            ));
        }

        $description = sprintf("  %s:%s",
            $this->i18n->__('scenario', 'Scenario'),
            $scenario->getTitle() ? ' ' . $scenario->getTitle() : ''
        );
        $this->output->write($description);
        $this->logLineSourceComment(mb_strlen($description), $this->file, $scenario->getLine());
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
        if ($this->inOutline) {
            if ($code != 'passed') {
                $this->outlineScenarioStepResults[] = array($code, $e);
            }
            $this->outlineStepsInfo[] = array($file, $line);
        }

        if (!$this->inOutline && (!$this->hasBackground || !$this->inBackground)) {
            $description = sprintf('    %s %s', $type, $text);
            $this->output->write(sprintf('<%s>%s</%s>', $code, $description, $code));

            if (null !== $file && null !== $line) {
                $this->logLineSourceComment(
                    mb_strlen($description),
                    $file, $line
                );
            } else {
                $this->output->writeln('');
            }

            if (count($args)) {
                $this->logStepArguments($code, $args);
            }

            if (null !== $e) {
                $this->logStepError($e);
            }
        }
    }

    /**
     * Prints comment line with source info
     *
     * @param   integer $lineLength current line current length
     * @param   string  $file       definition filename
     * @param   integer $line       definition line
     */
    protected function logLineSourceComment($lineLength, $file, $line)
    {
        $indent = $lineLength > $this->stepsMaxLength ? 0 : $this->stepsMaxLength - $lineLength;

        $this->output->writeln(sprintf("%s <comment># %s:%d</comment>",
            str_repeat(' ', $indent), $this->ltrimPaths($file), $line
        ));
    }

    /**
     * Prints exception to console
     *
     * @param   Exception   $e  exception instance
     */
    protected function logStepError(\Exception $e)
    {
        $error = $this->verbose ? $e->__toString() : $e->getMessage();
        $this->output->writeln(sprintf("      <failed>%s</failed>",
            strtr($this->ltrimPaths($error), array(
                "\n"    =>  "\n      ",
                "<"     =>  "[",
                ">"     =>  "]"
            ))
        ));
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
                    $code, $this->getTableString($argument, 6), $code
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
    protected function recalcStepsMaxLength(Section $scenario)
    {
        $max = $this->stepsMaxLength;

        if ($scenario instanceof ScenarioOutline) {
            $type = $this->i18n->__('scenario-outline', 'Scenario Outline') . ':';
        } else if ($scenario instanceof Scenario) {    
            $type = $this->i18n->__('scenario', 'Scenario') . ':';
        } else if ($scenario instanceof Background) {
            $type = $this->i18n->__('background', 'Background') . ':';
        }
        $scenarioDescription = $scenario->getTitle() ? $type . ' ' . $scenario->getTitle() : $type;

        if (($tmp = mb_strlen($scenarioDescription) + 2) > $max) {
            $max = $tmp;
        }
        foreach ($scenario->getSteps() as $step) {
            $stepDescription = $step->getType() . ' ' . $step->getText();
            if (($tmp = mb_strlen($stepDescription) + 4) > $max) {
                $max = $tmp;
            }
        }

        $this->stepsMaxLength = $max;
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
        }

        $this->output->writeln($stats->getTime() . 'ms');
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
    protected function getTableString(Table $table, $indent = 6)
    {
        return strtr(
            sprintf(str_repeat(' ', $indent).'%s', $table),
            array("\n" => "\n".str_repeat(' ', $indent))
        );
    }
}

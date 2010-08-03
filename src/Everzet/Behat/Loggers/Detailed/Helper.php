<?php

namespace Everzet\Behat\Loggers\Detailed;

use \Everzet\Gherkin\Structures\Section;
use \Everzet\Gherkin\Structures\Scenario\Scenario;
use \Everzet\Gherkin\Structures\Scenario\ScenarioOutline;
use \Everzet\Gherkin\Structures\Scenario\Background;
use \Everzet\Gherkin\Structures\Inline\PyString;
use \Everzet\Gherkin\Structures\Inline\Table;

class Helper
{
    /**
     * Returns formatted tag string, prepared for console output
     *
     * @param   Section $section    section instance
     * 
     * @return  string
     */
    public function getTagsString(Section $section)
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
    public function getPyString(PyString $pystring, $indent = 6)
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
    public function getTableString(Table $table, $indent = 6)
    {
        return strtr(
            sprintf(str_repeat(' ', $indent).'%s', $table),
            array("\n" => "\n".str_repeat(' ', $indent))
        );
    }

    /**
     * Calculates max step description size for scenario/background
     *
     * @param   Section $scenario   scenario for calculations
     * 
     * @return  integer             description length
     */
    public function calcStepsMaxLength(Section $scenario)
    {
        $max = 0;

        if ($scenario instanceof ScenarioOutline) {
            $type = $scenario->getI18n()->__('scenario-outline', 'Scenario Outline') . ':';
        } else if ($scenario instanceof Scenario) {    
            $type = $scenario->getI18n()->__('scenario', 'Scenario') . ':';
        } else if ($scenario instanceof Background) {
            $type = $scenario->getI18n()->__('background', 'Background') . ':';
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

        return $max;
    }

    /**
     * Returns comment line with source info
     *
     * @param   integer $lineLength     current line length
     * @param   integer $stepsMaxLength line max length
     * @param   string  $file           definition filename
     * @param   integer $line           definition line
     */
    public function getLineSourceComment($lineLength, $stepsMaxLength, $file, $line)
    {
        $indent = $lineLength > $stepsMaxLength ? 0 : $stepsMaxLength - $lineLength;

        return sprintf("%s <comment># %s:%d</comment>",
            str_repeat(' ', $indent), $file, $line
        );
    }
}

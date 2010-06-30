<?php

namespace Gherkin;

abstract class RegexHolder
{
    protected $tagKeyword       = '@';
    protected $tableSplitter    = '|';
    protected $feature          = 'Feature';
    protected $background       = 'Background';
    protected $scenario         = 'Scenario';
    protected $scenarioOutline  = 'Scenario Outline';
    protected $examples         = 'Examples';
    protected $stepTypes        = array('Given', 'Then', 'When', 'And', 'But');

    public function getTagKeyword()
    {
        return $this->tagKeyword;
    }

    public function getTableSplitter()
    {
        return $this->tableSplitter;
    }

    public function getStepTypes()
    {
        return $this->stepTypes;
    }

    private function getStepTypesRegex()
    {
        return implode('|', $this->stepTypes);
    }

    private function getNotOneOfRegex(array $names)
    {
        return sprintf("(?!\\s*%s)", implode("|\\s*", $names));
    }

    public function getFeatureRegex()
    {
        return '#^\s*' . $this->feature . '\:\s*(?P<title>.+?)?\s*$#';
    }

    public function getBackgroundRegex()
    {
        return '#^\s*' . $this->background . '\:\s*(?P<title>.+?)?\s*$#';
    }

    public function getScenarioRegex()
    {
        return '#^\s*' . $this->scenario . '\:(?:\s+(?P<title>.+?))?\s*$#';
    }

    public function getScenarioOutlineRegex()
    {
        return '#^\s*' . $this->scenarioOutline . '\:(?:\s+(?P<title>.+?))?\s*$#';
    }

    public function getExamplesRegex()
    {
        return '#^\s*' . $this->examples . '\:(?:\s+(?P<title>.+?))?\s*$#';
    }

    public function getTagsRegex()
    {
        return '#^\s*' . "\\" . $this->tagKeyword . '(?P<tags>.+?)\s*$#';
    }

    public function getDescriptionRegex()
    {
        $isNotOneOfRegex = $this->getNotOneOfRegex(
            array_merge(
                array($this->background, $this->scenarioOutline, $this->scenario,
                    "\\" . $this->tagKeyword, "\\" . $this->tableSplitter),
                $this->stepTypes
            )
        );

        return '#^\s*(?P<description>' . $isNotOneOfRegex . '.+?)\s*$#';
    }

    public function getStepsRegex()
    {
        return '#^\s*(?P<type>' . $this->getStepTypesRegex() . '?)\s+(?P<step>.+?)\s*$#';
    }

    public function getPyStringStarterRegex()
    {
        return '#^\s*\"\"\"\s*$#';
    }

    public function getTableRegex()
    {
        return "#^\s*\\" . $this->tableSplitter . "(?P<row>.+?)\\" . $this->tableSplitter . "\s*$#";
    }
}

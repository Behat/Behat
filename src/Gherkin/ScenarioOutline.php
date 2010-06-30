<?php

namespace Gherkin;

class ScenarioOutline extends Scenario
{
    protected $examples = array();

    public function setExamples(array $examples)
    {
        $this->examples = $examples;
    }

    public function hasExamples()
    {
        return count($this->examples) > 0;
    }

    public function getExamples()
    {
        return $this->examples;
    }
}

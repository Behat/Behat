<?php

namespace Gherkin;

class Background extends Section
{
    protected $steps = array();

    public function addSteps(array $steps)
    {
        foreach ($steps as $step)
        {
            $this->addStep($step);
        }
    }

    public function addStep(Step $step)
    {
        $this->steps[] = $step;
    }

    public function hasSteps()
    {
        return count($this->steps) > 0;
    }

    public function getSteps()
    {
        return $this->steps;
    }
}

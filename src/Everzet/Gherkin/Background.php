<?php

/*
 * This file is part of the BehaviorTester.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Everzet\Gherkin;

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

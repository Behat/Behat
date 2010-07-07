<?php

/*
 * This file is part of the BehaviorTester.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

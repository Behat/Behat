<?php

namespace Everzet\Gherkin;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Scenario Outline.
 *
 * @package     behat
 * @subpackage  Gherkin
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
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

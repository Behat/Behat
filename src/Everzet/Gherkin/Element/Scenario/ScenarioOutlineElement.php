<?php

namespace Everzet\Gherkin\Element\Scenario;

use \Everzet\Gherkin\Element\Inline\ExamplesElement;

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
class ScenarioOutlineElement extends ScenarioElement
{
    protected $examples;

    public function setExamples(ExamplesElement $examples)
    {
        $this->examples = $examples;
    }

    public function hasExamples()
    {
        return null !== $this->examples;
    }

    public function getExamples()
    {
        return $this->examples;
    }
}

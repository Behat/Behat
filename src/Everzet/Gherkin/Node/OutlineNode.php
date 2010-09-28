<?php

namespace Everzet\Gherkin\Node;

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
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutlineNode extends ScenarioNode
{
    protected $examples;

    public function setExamples(ExamplesNode $examples)
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

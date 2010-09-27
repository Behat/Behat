<?php

namespace Everzet\Gherkin\Node;

use Everzet\Gherkin\I18n;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Scenario.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ScenarioNode extends SectionNode
{
    protected $feature;
    protected $line;
    protected $steps = array();

    public function __construct(I18n $i18n, $file = null, $line = 0)
    {
        $this->line = $line;
        parent::__construct($i18n, $file);
    }

    public function setFeature(FeatureNode $feature)
    {
        $this->feature = $feature;
    }

    public function getFeature()
    {
        return $this->feature;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function setSteps(array $steps)
    {
        $this->steps = array();

        foreach ($steps as $step) {
            $this->addStep($step);
        }
    }

    public function addStep(StepNode $step)
    {
        $step->setParent($this);
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

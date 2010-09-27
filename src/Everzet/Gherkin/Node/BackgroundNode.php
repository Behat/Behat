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
 * Background.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BackgroundNode extends SectionNode
{
    protected $feature;
    protected $line;
    protected $steps = array();

    /**
     * Creates new instance
     *
     * @param   string  $line   parsed feature line
     */
    public function __construct($line = 0, I18n $i18n, $file = null)
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

    /**
     * Returns line definition number
     *
     * @return  integer
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Adds steps to background
     *
     * @param   array $steps  array of Step instances
     */
    public function addSteps(array $steps)
    {
        foreach ($steps as $step) {
            $this->addStep($step);
        }
    }

    /**
     * Adds step to background
     *
     * @param   Step  $step Step instance
     */
    public function addStep(StepNode $step)
    {
        $step->setParent($this);
        $this->steps[] = $step;
    }

    /**
     * Is Background has steps?
     *
     * @return  boolean     true if has
     */
    public function hasSteps()
    {
        return count($this->steps) > 0;
    }

    /**
     * Returns array of Step instances
     *
     * @return array
     */
    public function getSteps()
    {
        return $this->steps;
    }
}

<?php

namespace Everzet\Gherkin\Structures\Scenario;

use \Everzet\Gherkin\I18n;
use \Everzet\Gherkin\Structures\Section;
use \Everzet\Gherkin\Structures\Step;

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
 * @package     behat
 * @subpackage  Gherkin
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Background extends Section
{
    protected $steps = array();
    protected $line;

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
        foreach ($steps as $step)
        {
            $this->addStep($step);
        }
    }

    /**
     * Adds step to background
     *
     * @param   Step  $step Step instance
     */
    public function addStep(Step $step)
    {
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

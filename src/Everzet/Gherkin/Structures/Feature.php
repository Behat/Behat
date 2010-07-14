<?php

namespace Everzet\Gherkin\Structures;

use \Everzet\Gherkin\Structures\Section;
use \Everzet\Gherkin\Structures\Scenario\Background;
use \Everzet\Gherkin\Structures\Scenario\Scenario;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Feature.
 *
 * @package     behat
 * @subpackage  Gherkin
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Feature extends Section
{
    protected $description = array();
    protected $backgrounds = array();
    protected $scenarios = array();

    /**
     * Adds description lines to Feature
     *
     * @param   array $description  array of description lines
     */
    public function addDescriptions(array $description)
    {
        $this->description = array_merge($this->description, $description);
    }

    /**
     * Adds description line to Feature
     *
     * @param   string  $description  description line
     */
    public function addDescription($description)
    {
        $this->description[] = $description;
    }

    /**
     * Is Feature has description?
     *
     * @return  boolean   true if has
     */
    public function hasDescription()
    {
        return count($this->description) > 0;
    }

    /**
     * Returns description lines array
     *
     * @return  array
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns description lines as string
     *
     * @return  string
     */
    public function getDescriptionsAsString()
    {
        return implode("\n  ", $this->descriptions);
    }

    /**
     * Adds Background to Feature
     *
     * @param   Background  $background background instance
     */
    public function addBackground(Background $background)
    {
        $this->backgrounds[] = $background;
    }

    /**
     * Does Feature has backgrounds?
     *
     * @return  boolean true if has
     */
    public function hasBackgrounds()
    {
        return count($this->backgrounds) > 0;
    }

    /**
     * Returns Feature backgrounds
     *
     * @return  array array of Background instances
     */
    public function getBackgrounds()
    {
        return $this->backgrounds;
    }

    /**
     * Adds Scenario to Feature
     *
     * @param   Scenario  $scenario Scenario instance
     */
    public function addScenario(Scenario $scenario)
    {
        $this->scenarios[] = $scenario;
    }

    /**
     * Does Feature has scenarios?
     *
     * @return  boolean true if has
     */
    public function hasScenarios()
    {
        return count($this->scenarios) > 0;
    }

    /**
     * Returns Feature scenarios
     *
     * @return  array array of Scenario instances
     */
    public function getScenarios()
    {
        return $this->scenarios;
    }
}

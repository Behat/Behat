<?php

namespace Everzet\Gherkin\Element;

use Everzet\Gherkin\Element\Scenario\BackgroundElement;
use Everzet\Gherkin\Element\Scenario\ScenarioElement;

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
 * @package     Gherkin
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeatureElement extends SectionElement
{    
    protected $background;
    protected $description = array();
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
    public function setBackground(BackgroundElement $background)
    {
        $this->background = $background;
    }

    /**
     * Does Feature has background?
     *
     * @return  boolean true if has
     */
    public function hasBackground()
    {
        return null !== $this->background;
    }

    /**
     * Returns Feature backgrounds
     *
     * @return  array array of Background instances
     */
    public function getBackground()
    {
        return $this->background;
    }

    /**
     * Adds Scenario to Feature
     *
     * @param   Scenario  $scenario Scenario instance
     */
    public function addScenario(ScenarioElement $scenario)
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

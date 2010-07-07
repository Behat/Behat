<?php

/*
 * This file is part of the BehaviorTester.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gherkin;

class Feature extends Section
{
    protected $description = array();
    protected $backgrounds = array();
    protected $scenarios = array();

    public function addDescriptions(array $description)
    {
        $this->description = array_merge($this->description, $description);
    }

    public function addDescription($description)
    {
        $this->description[] = $description;
    }

    public function hasDescription()
    {
        return count($this->description) > 0;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getDescriptionsAsString()
    {
        return implode("\n  ", $this->descriptions);
    }

    public function addBackground(Background $background)
    {
        $this->backgrounds[] = $background;
    }

    public function hasBackgrounds()
    {
        return count($this->backgrounds) > 0;
    }

    public function getBackgrounds()
    {
        return $this->backgrounds;
    }

    public function addScenario(Scenario $scenario)
    {
        $this->scenarios[] = $scenario;
    }

    public function hasScenarios()
    {
        return count($this->scenarios) > 0;
    }

    public function getScenarios()
    {
        return $this->scenarios;
    }
}

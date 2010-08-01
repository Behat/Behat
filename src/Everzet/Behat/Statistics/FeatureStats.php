<?php

namespace Everzet\Behat\Stats;

use \Everzet\Behat\Stats\ScenarioStats;

class FeatureStats
{
    protected $scenariosStats = array();

    public function addScenarioStatuses(ScenarioStats $scenarioStats)
    {
        $this->scenariosStats[] = $scenarioStats;
    }

    public function getStepsCount()
    {
        $count = 0;
        foreach ($this->scenariosStats as $scenarioStats) {
            $count += $scenarioStats->getStepsCount();
        }

        return $count;
    }

    public function getScenariosCount()
    {
        return count($this->scenariosStats);
    }

    public function getScenarioStatusCount($status)
    {
        $count = 0;
        foreach ($this->scenariosStats as $scenarioStats) {
            $count += $scenarioStats->getScenarioStatusCount($status);
        }

        return $count;
    }

    public function getStepStatusCount($status)
    {
        $count = 0;
        foreach ($this->scenariosStats as $scenarioStats) {
            $count += $scenarioStats->getStepStatusCount($status);
        }

        return $count;
    }
}

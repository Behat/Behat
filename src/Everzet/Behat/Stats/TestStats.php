<?php

namespace Everzet\Behat\Stats;

class TestStats
{
    protected $featuresStats = array();

    public function addFeatureStatuses(FeatureStats $featureStats)
    {
        $this->featuresStats[] = $featureStats;
    }

    public function getStepsCount()
    {
        $count = 0;
        foreach ($this->featuresStats as $featureStats) {
            $count += $featureStats->getStepsCount();
        }

        return $count;
    }

    public function getScenariosCount()
    {
        $count = 0;
        foreach ($this->featuresStats as $featureStats) {
            $count += $featureStats->getScenariosCount();
        }

        return $count;
    }

    public function getScenarioStatusCount($status)
    {
        $count = 0;
        foreach ($this->featuresStats as $featureStats) {
            $count += $featureStats->getScenarioStatusCount($status);
        }

        return $count;
    }

    public function getStepStatusCount($status)
    {
        $count = 0;
        foreach ($this->featuresStats as $featureStats) {
            $count += $featureStats->getStepStatusCount($status);
        }

        return $count;
    }

    public function getStatisticStatusTypes()
    {
        return array(
            'pending', 'failed', 'undefined', 'passed'
        );
    }
}

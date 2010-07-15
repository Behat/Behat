<?php

namespace Everzet\Behat\Stats;

use \Everzet\Behat\Stats\FeatureStats;

class TestStats
{
    protected $featuresStats = array();
    protected $startTime;
    protected $resultTime;

    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    public function getTime()
    {
        return sprintf('%.0f', (microtime(true) - $this->startTime) * 1000);
    }

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

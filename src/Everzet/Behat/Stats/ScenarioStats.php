<?php

namespace Everzet\Behat\Stats;

class ScenarioStats
{
    protected $statuses = array();

    public function addStepStatus($status)
    {
        $this->statuses[] = $status;
    }

    public function getLastStepStatus()
    {
        return end($this->statuses);
    }

    public function getStatuses()
    {
        return $this->statuses;
    }

    public function getStepsCount()
    {
        return count($this->statuses);
    }

    public function mergeStatuses(ScenarioStats $stats)
    {
        $this->statuses = array_merge($this->statuses, $stats->getStatuses());
    }

    public function getScenarioStatusCount($status)
    {
        if ('passed' === $status) {
            foreach ($this->statuses as $currentStatus) {
                if ('passed' !== $currentStatus) {
                    return 0;
                }
            }

            return 1;
        } else {
            return intval(in_array($status, $this->statuses));
        }
    }

    public function getStepStatusCount($status)
    {
        $count = 0;
        foreach ($this->statuses as $currentStatus) {
            if ($currentStatus === $status) {
                $count++;
            }
        }

        return $count;
    }
}

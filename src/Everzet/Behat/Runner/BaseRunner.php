<?php

namespace Everzet\Behat\Runner;

use Everzet\Behat\Logger\LoggerInterface;

abstract class BaseRunner
{
    protected $caller;

    protected function getStatusFromArray(array $items)
    {
        $status = 'passed';

        foreach ($items as $item) {
            $code = $this->getHigherStatus($status, $item->getStatus());
        }

        return $status;
    }

    protected function getHigherStatus($lftStatus, $rgtStatus)
    {
        $statuses   = array('passed', 'pending', 'undefined', 'failed');
        $code       = array_search($lftStatus, $statuses);

        if (($rgtCode = array_search($rgtStatus, $statuses)) > $code) {
            $code = $rgtCode;
        }

        return $statuses[$code];
    }

    protected function setCaller(RunnerInterface $caller = null)
    {
        $this->caller = $caller;
    }

    public function getCaller()
    {
        return $this->caller;
    }
}

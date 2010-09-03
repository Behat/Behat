<?php

namespace Everzet\Behat\Runner;

use \Everzet\Behat\Logger\LoggerInterface;

abstract class BaseRunner
{
    protected $logger;
    protected $caller;

    protected function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    protected function getLogger()
    {
        return $this->logger;
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

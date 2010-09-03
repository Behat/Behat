<?php

namespace Everzet\Behat\Runners;

use \Everzet\Behat\Loggers\Logger;

abstract class BaseRunner
{
    protected $logger;
    protected $caller;

    protected function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    protected function getLogger()
    {
        return $this->logger;
    }

    protected function setCaller(Runner $caller = null)
    {
        $this->caller = $caller;
    }

    public function getCaller()
    {
        return $this->caller;
    }
}

<?php

namespace Everzet\Behat\Runner;

use Everzet\Behat\Logger\LoggerInterface;

abstract class BaseRunner
{
    protected $caller;

    protected function setCaller(RunnerInterface $caller = null)
    {
        $this->caller = $caller;
    }

    public function getCaller()
    {
        return $this->caller;
    }
}

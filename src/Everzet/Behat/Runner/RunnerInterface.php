<?php

namespace Everzet\Behat\Runner;

interface RunnerInterface
{
    public function run(RunnerInterface $caller = null);
    public function getStatus();
}

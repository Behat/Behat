<?php

namespace Everzet\Behat\Runner;

interface RunnerInterface
{
    public function run(RunnerInterface $caller = null);
}

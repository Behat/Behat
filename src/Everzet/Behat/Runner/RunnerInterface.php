<?php

namespace Everzet\Behat\Runner;

interface RunnerInterface
{
    public function run();
    public function getStatus();
    public function getStatusCode();
}

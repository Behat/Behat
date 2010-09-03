<?php

namespace Everzet\Behat\Runners;

interface Runner
{
    public function run(Runner $caller = null);
}

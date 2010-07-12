<?php

namespace Behat\Console;

use \Symfony\Components\Console\Application as BaseApplication;
use \Symfony\Components\Console\Output\Output;

class BehatApplication extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('BehaviorTester', '0.1');

        $this->addCommands(array(
            new \Behat\Console\Commands\TestCommand()
        ));
    }
}

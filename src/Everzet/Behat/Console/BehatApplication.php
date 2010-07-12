<?php

namespace Everzet\Behat\Console;

use \Symfony\Components\Console\Application as BaseApplication;

use \Everzet\Behat\Console\Commands\TestCommand;

class BehatApplication extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('BehaviorTester', '0.1');

        $this->addCommands(array(
            new TestCommand()
        ));
    }
}

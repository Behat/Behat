<?php

namespace Behat\Console;

use \Symfony\Components\Console\Application as BaseApplication;
use \Symfony\Components\Console\Output\Output;

class BehatApplication extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('BehaviorTester', '0.1');

        Output::setStyle('failed',      array('fg' => 'red'));
        Output::setStyle('undefined',   array('fg' => 'yellow'));
        Output::setStyle('pending',     array('fg' => 'yellow'));
        Output::setStyle('passed',      array('fg' => 'green'));
        Output::setStyle('skipped',     array('fg' => 'cyan'));
        Output::setStyle('comment',     array('fg' => 'black'));
        Output::setStyle('tag',         array('fg' => 'cyan'));

        $this->addCommands(array(
            new \Behat\Console\Commands\TestCommand()
        ));
    }
}

<?php

namespace BehaviorTester;

class Command extends \PHPUnit_TextUI_Command
{
    public function __construct()
    {
        $this->arguments['printer'] = new \BehaviorTester\ResultPrinter\Text;
    }

    /**
     * @param boolean $exit
     */
    public static function main($exit = TRUE)
    {
        $command = new self;
        $command->run($_SERVER['argv'], $exit);
    }
}

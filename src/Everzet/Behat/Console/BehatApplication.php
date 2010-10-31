<?php

namespace Everzet\Behat\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Everzet\Behat\Console\Command\BehatCommand;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat main application.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BehatApplication extends Application
{
    /**
     * @see     Symfony\Component\Console\Application
     */
    public function __construct()
    {
        parent::__construct('Behat', 'DEV');

        $this->definition = new InputDefinition();
        $this->addCommands(array(new BehatCommand()));
    }

    /**
     * @see     Symfony\Component\Console\Application
     */
    protected function getCommandName(InputInterface $input)
    {
        return 'behat';
    }

    /**
     * @see     Symfony\Component\Console\Application 
     */
    public function renderException($e, $output)
    {
        $this->runningCommand = null;

        parent::renderException($e, $output);
    }
}


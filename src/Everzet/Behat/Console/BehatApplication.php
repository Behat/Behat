<?php

namespace Everzet\Behat\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Everzet\Behat\Console\Command\TestCommand;

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
        parent::__construct('Behat','0.1.5');

        $this->definition = new InputDefinition(array(
            new InputOption('--help',           '-H', InputOption::PARAMETER_NONE, 'Display this help message.'),
            new InputOption('--quiet',          '-q', InputOption::PARAMETER_NONE, 'Do not output any message.'),
            new InputOption('--verbose',        '-v', InputOption::PARAMETER_NONE, 'Increase verbosity of messages.'),
            new InputOption('--version',        '-V', InputOption::PARAMETER_NONE, 'Display this program version.'),
            new InputOption('--ansi',           '-a', InputOption::PARAMETER_NONE, 'Force ANSI output.'),
            new InputOption('--no-interaction', '-n', InputOption::PARAMETER_NONE, 'Do not ask any interactive question.'),
        ));

        $this->addCommands(array(
            new TestCommand()
        ));
    }

    /**
     * @see     Symfony\Component\Console\Application
     */
    protected function getCommandName(InputInterface $input)
    {
        return 'test';
    }
}

<?php

namespace Behat\Behat\Console;

use Symfony\Component\Console\Application,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputDefinition,
    Symfony\Component\Console\Input\InputOption;

use Behat\Behat\Console\Command\BehatCommand;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat console application.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BehatApplication extends Application
{
    /**
     * {@inheritdoc}
     */
    public function __construct($version)
    {
        parent::__construct('Behat', $version);

        $this->add(new BehatCommand());
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new InputDefinition(array(
            new InputOption('--help',       '-h', InputOption::VALUE_NONE, 'Display this help message.'),
            new InputOption('--verbose',    '-v', InputOption::VALUE_NONE, 'Increase verbosity of exceptions.'),
            new InputOption('--version',    '-V', InputOption::VALUE_NONE, 'Display this behat version.'),
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandName(InputInterface $input)
    {
        return 'behat';
    }
}

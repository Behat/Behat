<?php

namespace Behat\Behat\Console;

use Symfony\Component\Console\Application,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputDefinition;

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
    public function __construct()
    {
        parent::__construct('Behat', 'DEV');

        $this->definition = new InputDefinition();
        $this->addCommands(array(new BehatCommand()));
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandName(InputInterface $input)
    {
        return 'behat';
    }

    /**
     * {@inheritdoc}
     */
    public function renderException($e, $output)
    {
        $this->runningCommand = null;

        parent::renderException($e, $output);
    }
}

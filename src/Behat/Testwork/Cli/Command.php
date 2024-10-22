<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Cli;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Extends Symfony console command with a controller-based delegation.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Command extends BaseCommand
{
    /**
     * @var Controller[]
     */
    private $controllers = array();

    /**
     * Initializes command.
     *
     * @param string       $commandName
     * @param Controller[] $controllers
     */
    public function __construct($commandName, array $controllers)
    {
        $this->controllers = $controllers;

        parent::__construct($commandName);
    }

    /**
     * Configures the command by running controllers prepare().
     */
    protected function configure()
    {
        foreach ($this->controllers as $controller) {
            $controller->configure($this);
        }
    }

    /**
     * Executes the current command by executing all controllers action().
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return integer Return code of one of the processors or 0 if none of them returned integer
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->controllers as $controller) {
            if (is_int($return = $controller->execute($input, $output))) {
                return $return;
            }
        }

        return 0;
    }
}

<?php

namespace Behat\Behat\Console\Processor;

use Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat console processor interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ProcessorInterface
{
    /**
     * Configures command to be able to process it later.
     *
     * @param Command $command
     */
    public function configure(Command $command);

    /**
     * Processes data from container and console input.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function process(InputInterface $input, OutputInterface $output);
}

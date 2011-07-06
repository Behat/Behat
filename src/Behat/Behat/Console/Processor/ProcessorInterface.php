<?php

namespace Behat\Behat\Console\Processor;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Command\Command,
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
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ProcessorInterface
{
    /**
     * Configures command to be able to process it later.
     *
     * @param   Symfony\Component\Console\Command\Command   $command    command instance to configure
     */
    function configure(Command $command);

    /**
     * Processes data from container and console input.
     *
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     * @param   Symfony\Component\Console\Input\InputInterface              $input      console input
     * @param   Symfony\Component\Console\Output\OutputInterface            $output     console output
     */
    function process(ContainerInterface $container, InputInterface $input, OutputInterface $output);
}

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
 * Processors manager.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class AggregateProcessor implements ProcessorInterface
{
    private $processors = array();

    /**
     * Adds processor to the manager.
     *
     * @param ProcessorInterface $processor Processor instance
     */
    public function addProcessor(ProcessorInterface $processor)
    {
        $this->processors[] = $processor;
    }

    /**
     * Configures command to be able to process it later.
     *
     * @param Command $command
     */
    public function configure(Command $command)
    {
        foreach ($this->processors as $processor) {
            $processor->configure($command);
        }
    }

    /**
     * Processes data from container and console input.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function process(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->processors as $processor) {
            $processor->process($input, $output);
        }
    }
}

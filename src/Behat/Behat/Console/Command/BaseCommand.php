<?php

namespace Behat\Behat\Console\Command;

use Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\Console\Processor\ProcessorInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base behat console command.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class BaseCommand extends Command
{
    private $processor;

    /**
     * Returns service container instance.
     *
     * @return ContainerInterface
     */
    abstract protected function getContainer();

    /**
     * Sets command processor.
     *
     * @param ProcessorInterface $processor
     *
     * @return Command
     */
    protected function setProcessor(ProcessorInterface $processor)
    {
        $this->processor = $processor;
        $this->processor->configure($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->processor->process($input, $output);
    }
}

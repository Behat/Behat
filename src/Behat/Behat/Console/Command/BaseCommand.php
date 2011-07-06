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
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class BaseCommand extends Command
{
    /**
     * List of command processors.
     *
     * @var     array
     */
    private $processors = array();

    /**
     * Sets command processors.
     *
     * @param   array   $processors
     */
    protected function setProcessors(array $processors)
    {
        $this->processors = array();
        foreach ($processors as $processor) {
            $this->addProcessor($processor);
        }
    }

    /**
     * Adds single processor to the list.
     *
     * @param   Behat\Behat\Console\Processor\ProcessorInterface    $processor
     */
    protected function addProcessor(ProcessorInterface $processor)
    {
        $this->processors[] = $processor;
    }

    /**
     * Configures processors with current command.
     */
    protected function configureProcessors()
    {
        foreach ($this->processors as $processor) {
            $processor->configure($this);
        }
    }

    /**
     * Returns list of processors.
     *
     * @return  array
     */
    protected function getProcessors()
    {
        return $this->processors;
    }

    /**
     * Returns service container instance.
     *
     * @return  Symfony\Component\DependencyInjection\ContainerInterface
     */
    abstract protected function getContainer();

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        foreach ($this->getProcessors() as $processor) {
            $processor->process($container, $input, $output);
        }
    }
}

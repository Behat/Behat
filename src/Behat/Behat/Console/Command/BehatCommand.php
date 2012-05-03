<?php

namespace Behat\Behat\Console\Command;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\Console\Input\InputDefinition,
    Behat\Behat\Console\Processor\ProcessorInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat console command.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BehatCommand extends BaseCommand
{
    private $container;

    /**
     * Initializes command.
     *
     * @param ContainerInterface $container
     * @param ProcessorInterface $processor
     */
    public function __construct(ContainerInterface $container, ProcessorInterface $processor)
    {
        parent::__construct('behat');

        $this->container = $container;
        $this->setDefinition(new InputDefinition);
        $this->setProcessor($processor);
    }

    /**
     * Returns container instance.
     *
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }
}

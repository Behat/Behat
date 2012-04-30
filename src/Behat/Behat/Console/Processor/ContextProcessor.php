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
 * Context processor.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextProcessor extends Processor
{
    private $container;

    /**
     * Constructs processor.
     *
     * @param ContainerInterface $container Container instance
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @see ProcessorInterface::process()
     */
    public function process(InputInterface $input, OutputInterface $output)
    {
        $contextClass = $this->container->getParameter('behat.context.class');
        $this->container->get('behat.runner')->setMainContextClass($contextClass);
    }
}

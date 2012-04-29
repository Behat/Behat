<?php

namespace Behat\Behat\Console\Command;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\Console\Input\InputDefinition;

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
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BehatCommand extends BaseCommand
{
    /**
     * Service container.
     *
     * @var     Symfony\Component\DependencyInjection\ContainerBuilder
     */
    private $container;

    public function __construct(ContainerInterface $container, array $processors)
    {
        parent::__construct('behat');

        $this->container = $container;
        $this->setDefinition(new InputDefinition);
        $this->setProcessors($processors);
        $this->configureProcessors();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->getContainer()->get('behat.runner')->runSuite();
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

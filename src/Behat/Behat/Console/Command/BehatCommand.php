<?php

namespace Behat\Behat\Console\Command;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\Console\Processor,
    Behat\Behat\Console\Input\InputDefinition;

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

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->container = new ContainerBuilder();

        $this
            ->setName('behat')
            ->setDefinition(new InputDefinition)
            ->setProcessors(array(
                new Processor\ContainerProcessor(),
                new Processor\LocatorProcessor(),
                new Processor\InitProcessor(),
                new Processor\ContextProcessor(),
                new Processor\FormatProcessor(),
                new Processor\HelpProcessor(),
                new Processor\GherkinProcessor(),
                new Processor\RunProcessor(),
            ))
            ->addArgument('features', InputArgument::OPTIONAL,
                "Feature(s) to run. Could be:\n" .
                "- a dir <comment>(features/)</comment>\n" .
                "- a feature <comment>(*.feature)</comment>\n" .
                "- a scenario at specific line <comment>(*.feature:10)</comment>.\n" .
                "- all scenarios at or after a specific line <comment>(*.feature:10-*)</comment>.\n" .
                "- all scenarios at a line within a specific range <comment>(*.feature:10-20)</comment>."
            )
            ->configureProcessors()
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->getContainer()->get('behat.runner')->runSuite();
    }
}

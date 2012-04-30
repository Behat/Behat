<?php

namespace Behat\Behat\Console\Processor;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputArgument,
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
 * Path locator processor.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class LocatorProcessor extends Processor
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
     * @see ProcessorInterface::command()
     */
    public function configure(Command $command)
    {
        $command->addArgument('features', InputArgument::OPTIONAL,
            "Feature(s) to run. Could be:\n" .
            "- a dir <comment>(features/)</comment>\n" .
            "- a feature <comment>(*.feature)</comment>\n" .
            "- a scenario at specific line <comment>(*.feature:10)</comment>.\n" .
            "- all scenarios at or after a specific line <comment>(*.feature:10-*)</comment>.\n" .
            "- all scenarios at a line within a specific range <comment>(*.feature:10-20)</comment>."
        );
    }

    /**
     * @see ProcessorInterface::process()
     */
    public function process(InputInterface $input, OutputInterface $output)
    {
        $this->container->get('behat.runner')->setLocatorBasePath($input->getArgument('features'));
        $locator = $this->container->get('behat.path_locator');

        foreach ($locator->locateBootstrapFilesPaths() as $path) {
            require_once($path);
        }

        if (!($input->hasOption('init') && $input->getOption('init'))
         && !is_dir($featuresPath = $locator->getFeaturesPath())) {
            throw new \InvalidArgumentException("Features path \"$featuresPath\" does not exist");
        }
    }
}

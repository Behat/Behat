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
 * Path locator processor.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class LocatorProcessor implements ProcessorInterface
{
    /**
     * @see     Behat\Behat\Console\Configuration\ProcessorInterface::command()
     */
    public function configure(Command $command)
    {
    }

    /**
     * @see     Behat\Behat\Console\Configuration\ProcessorInterface::process()
     */
    public function process(ContainerInterface $container, InputInterface $input, OutputInterface $output)
    {
        $container->get('behat.runner')->setLocatorBasePath($input->getArgument('features'));
        $locator = $container->get('behat.path_locator');

        foreach ($locator->locateBootstrapFilesPaths() as $path) {
            require_once($path);
        }

        if (!($input->hasOption('init') && $input->getOption('init'))
         && !is_dir($featuresPath = $locator->getFeaturesPath())) {
            throw new \InvalidArgumentException("Features path \"$featuresPath\" does not exist");
        }
    }
}

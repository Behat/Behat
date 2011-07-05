<?php

namespace Behat\Behat\Console\Processor;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

class LocatorProcessor implements ProcessorInterface
{
    /**
     * @see     Behat\Behat\Console\Configuration\ProcessorInterface::getInputOptions()
     */
    public function getInputOptions()
    {
        return array();
    }

    /**
     * @see     Behat\Behat\Console\Configuration\ProcessorInterface::process()
     */
    public function process(ContainerInterface $container, InputInterface $input, OutputInterface $output)
    {
        $locator = $container->get('behat.path_locator');

        // locate base path
        $locator->locateBasePath($input->getArgument('features'));

        // load bootstrap files
        foreach ($locator->locateBootstrapFilesPaths() as $path) {
            require_once($path);
        }

        // we don't want to init, so we check, that features path exists
        if (!($input->hasOption('init') && $input->getOption('init'))
         && !is_dir($featuresPath = $locator->getFeaturesPath())) {
            throw new \InvalidArgumentException("Features path \"$featuresPath\" does not exist");
        }
    }
}

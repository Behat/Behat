<?php

namespace Behat\Behat\Console;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Application,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputDefinition,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\DependencyInjection\BehatExtension,
    Behat\Behat\DependencyInjection\Configuration\Loader;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat console application.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BehatApplication extends Application
{
    /**
     * {@inheritdoc}
     */
    public function __construct($version)
    {
        parent::__construct('Behat', $version);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new InputDefinition(array(
            new InputOption('--help',    '-h', InputOption::VALUE_NONE, 'Display this help message.'),
            new InputOption('--verbose', '-v', InputOption::VALUE_NONE, 'Increase verbosity of exceptions.'),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this behat version.'),
            new InputOption('--config',  '-c', InputOption::VALUE_REQUIRED, 'Specify config file to use.'),
            new InputOption('--profile', '-p', InputOption::VALUE_REQUIRED, 'Specify config profile to use.')
        ));
    }

    /**
     * Runs the current application.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return integer 0 if everything went fine, or an error code
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        // construct container
        $container = new ContainerBuilder();
        $this->loadConfiguration($container, $input);
        $container->compile();

        // setup command into application
        $this->add($container->get('behat.console.command'));

        return parent::doRun($input, $output);
    }

    /**
     * Configures container based on providen config file and profile.
     *
     * @param ContainerInterface $container
     * @param InputInterface     $input
     */
    protected function loadConfiguration(ContainerInterface $container, InputInterface $input)
    {
        $file    = $input->getParameterOption(array('--config', '-c'));
        $profile = $input->getParameterOption(array('--profile', '-p')) ?: 'default';
        $cwd     = getcwd();

        // if config file is not provided
        if (!$file) {
            // then use behat.yml
            if (is_file($cwd.DIRECTORY_SEPARATOR.'behat.yml')) {
                $file = $cwd.DIRECTORY_SEPARATOR.'behat.yml';
            // or behat.yml.dist
            } elseif (is_file($cwd.DIRECTORY_SEPARATOR.'behat.yml.dist')) {
                $file = $cwd.DIRECTORY_SEPARATOR.'behat.yml.dist';
            // or config/behat.yml
            } elseif (is_file($cwd.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'behat.yml')) {
                $file = $cwd.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'behat.yml';
            }
        }

        // read configuration
        $loader  = new Loader($file);
        $configs = $loader->loadConfiguration($profile);

        // locate base path
        $basePath = $cwd;
        if (file_exists($file)) {
            $basePath = realpath(dirname($file));
        }

        // load core extension into temp container
        $extension = new BehatExtension($basePath);
        $extension->load($configs, $container);
        $container->addObjectResource($extension);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandName(InputInterface $input)
    {
        return 'behat';
    }

    /**
     * {@inheritdoc}
     */
    protected function getTerminalWidth()
    {
        return PHP_INT_MAX;
    }
}

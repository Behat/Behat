<?php

namespace Behat\Behat\Console;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Application,
    Symfony\Component\Console\Command\Command,
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
    private $basePath;

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
        $this->add($this->createCommand($input));

        return parent::doRun($input, $output);
    }

    /**
     * Creates main command for application.
     *
     * @param InputInterface $input
     *
     * @return Command
     */
    protected function createCommand(InputInterface $input)
    {
        return $this->createContainer($input)->get('behat.console.command');
    }

    /**
     * Creates container instance, loads extensions and freezes it.
     *
     * @param InputInterface $input
     *
     * @return ContainerInterface
     */
    protected function createContainer(InputInterface $input)
    {
        $container = new ContainerBuilder();
        $this->loadCoreExtension($container, $this->loadConfiguration($container, $input));
        $container->compile();

        return $container;
    }

    /**
     * Configures container based on providen config file and profile.
     *
     * @param ContainerBuilder $container
     * @param InputInterface   $input
     */
    protected function loadConfiguration(ContainerBuilder $container, InputInterface $input)
    {
        // locate paths
        $this->basePath = getcwd();
        if ($configPath = $this->getConfigurationFilePath($input)) {
            $this->basePath = realpath(dirname($configPath));
        }

        // read configuration
        $loader  = new Loader($configPath);
        $profile = $input->getParameterOption(array('--profile', '-p')) ?: 'default';

        return $loader->loadConfiguration($profile);
    }

    /**
     * Finds configuration file and returns path to it.
     *
     * @param InputInterface $input
     *
     * @return string
     */
    protected function getConfigurationFilePath(InputInterface $input)
    {
        // custom configuration file
        if ($file = $input->getParameterOption(array('--config', '-c'))) {
            if (is_file($file)) {
                return $file;
            }

            return;
        }

        // predefined config paths
        $cwd = rtrim(getcwd(), DIRECTORY_SEPARATOR);
        foreach (array_filter(array(
            $cwd.DIRECTORY_SEPARATOR.'behat.yml',
            $cwd.DIRECTORY_SEPARATOR.'behat.yml.dist',
            $cwd.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'behat.yml',
            $cwd.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'behat.yml.dist',
        ), 'is_file') as $path) {
            return $path;
        }
    }

    /**
     * Loads core extension into container.
     *
     * @param ContainerBuilder $container
     * @param $array           $configs
     */
    protected function loadCoreExtension(ContainerBuilder $container, array $configs)
    {
        if (null === $this->basePath) {
            throw new \RuntimeException(
                'Suite basepath is not set. Seems you have forgot to load configuration first.'
            );
        }

        $extension = new BehatExtension($this->basePath);
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

<?php

namespace Behat\Behat\Console;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\DependencyInjection\BehatExtension;
use Behat\Behat\DependencyInjection\Configuration\Loader;
use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Behat console application.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BehatApplication extends Application
{
    private $basePath;

    /**
     * Initializes application.
     *
     * @param string $version The version of the application
     */
    public function __construct($version)
    {
        parent::__construct('behat', $version);
    }

    /**
     * Gets the default input definition.
     *
     * @return InputDefinition An InputDefinition instance
     */
    public function getDefaultInputDefinition()
    {
        return new InputDefinition(array(
            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message.'),
            new InputOption('--verbose', '-v', InputOption::VALUE_NONE, 'Increase verbosity of exceptions.'),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this behat version.'),
            new InputOption('--config', '-c', InputOption::VALUE_REQUIRED, 'Specify config file to use.'),
            new InputOption('--profile', '-p', InputOption::VALUE_REQUIRED, 'Specify config profile to use.')
        ));
    }

    /**
     * Runs the current application.
     *
     * @param InputInterface  $input  An Input instance
     * @param OutputInterface $output An Output instance
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
        return $this->createContainer($input)->get('console.command');
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
        $this->loadCoreExtension($container, $this->loadConfiguration($input));
        $container->compile();

        return $container;
    }

    /**
     * Configures container based on provided config file and profile.
     *
     * @param InputInterface $input
     *
     * @return array
     */
    protected function loadConfiguration(InputInterface $input)
    {
        // locate paths
        $this->basePath = getcwd();
        if ($configPath = $this->getConfigurationFilePath($input)) {
            $this->basePath = realpath(dirname($configPath));
        }

        // read configuration
        $loader = new Loader($configPath);
        $profile = $input->getParameterOption(array('--profile', '-p')) ? : 'default';

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

            return null;
        }

        // predefined config paths
        $cwd = rtrim(getcwd(), DIRECTORY_SEPARATOR);
        $paths = array_filter(
            array(
                $cwd . DIRECTORY_SEPARATOR . 'behat.yml',
                $cwd . DIRECTORY_SEPARATOR . 'behat.yml.dist',
                $cwd . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'behat.yml',
                $cwd . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'behat.yml.dist',
            ),
            'is_file'
        );
        if (count($paths)) {
            return current($paths);
        }

        return null;
    }

    /**
     * Loads core extension into container.
     *
     * @param ContainerBuilder $container
     * @param array            $configs
     *
     * @throws RuntimeException
     */
    protected function loadCoreExtension(ContainerBuilder $container, array $configs)
    {
        if (null === $this->basePath) {
            throw new RuntimeException(
                'Suite base path is not set. Seems you have forgot to load configuration first.'
            );
        }

        $extension = new BehatExtension($this->basePath);
        $extension->load($configs, $container);
        $container->addObjectResource($extension);
    }

    /**
     * Gets the name of the command based on input.
     *
     * @param InputInterface $input The input interface
     *
     * @return string The command name
     */
    protected function getCommandName(InputInterface $input)
    {
        return 'behat';
    }

    /**
     * Tries to figure out the terminal width in which this application runs
     *
     * @return int|null
     */
    protected function getTerminalWidth()
    {
        return PHP_INT_MAX;
    }
}

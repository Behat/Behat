<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Cli;

use Behat\Testwork\ServiceContainer\Configuration\ConfigurationLoader;
use Behat\Testwork\ServiceContainer\ContainerLoader;
use Behat\Testwork\ServiceContainer\Exception\ConfigurationLoadingException;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extends Symfony console application with testwork functionality.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class Application extends BaseApplication
{
    /**
     * @var ConfigurationLoader
     */
    private $configurationLoader;
    /**
     * @var ExtensionManager
     */
    private $extensionManager;

    /**
     * Initializes application.
     *
     * @param string              $name
     * @param string              $version
     * @param ConfigurationLoader $configLoader
     * @param ExtensionManager    $extensionManager
     */
    public function __construct($name, $version, ConfigurationLoader $configLoader, ExtensionManager $extensionManager)
    {
        putenv('COLUMNS=9999');

        $this->configurationLoader = $configLoader;
        $this->extensionManager = $extensionManager;

        parent::__construct($name, $version);
    }

    /**
     * Gets the default input definition.
     *
     * @return InputDefinition An InputDefinition instance
     */
    public function getDefaultInputDefinition(): InputDefinition
    {
        return new InputDefinition(array(
            new InputOption('--profile', '-p', InputOption::VALUE_REQUIRED, 'Specify config profile to use.'),
            new InputOption('--config', '-c', InputOption::VALUE_REQUIRED, 'Specify config file to use.'),
            new InputOption(
                '--verbose', '-v', InputOption::VALUE_OPTIONAL,
                'Increase verbosity of exceptions.' . PHP_EOL .
                'Use -vv or --verbose=2 to display backtraces in addition to exceptions.'
            ),
            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message.'),
            new InputOption('--config-reference', null, InputOption::VALUE_NONE, 'Display the configuration reference.'),
            new InputOption('--debug', null, InputOption::VALUE_NONE, 'Provide debugging information about current environment.'),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display version.'),
            new InputOption('--no-interaction', '-n', InputOption::VALUE_NONE, 'Do not ask any interactive question.'),
            new InputOption(
                '--colors', null, InputOption::VALUE_NONE,
                'Force ANSI color in the output. By default color support is' . PHP_EOL .
                'guessed based on your platform and the output if not specified.'
            ),
            new InputOption('--no-colors', null, InputOption::VALUE_NONE, 'Force no ANSI color in the output.'),
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
    public function doRun(InputInterface $input, OutputInterface $output): int
    {
        // xdebug's default nesting level of 100 is not enough
        if (extension_loaded('xdebug')
            && false === strpos(ini_get('disable_functions'), 'ini_set')
        ) {
            $oldValue = ini_get('xdebug.max_nesting_level');
            if ($oldValue === false || $oldValue < 256) {
                ini_set('xdebug.max_nesting_level', 256);
            }
        }

        if ($input->hasParameterOption(array('--config-reference'))) {
            $input = new ArrayInput(array('--config-reference' => true));
        }

        if ($path = $input->getParameterOption(array('--config', '-c'))) {
            if (!is_file($path)) {
                throw new ConfigurationLoadingException("The requested config file does not exist");
            }

            $this->configurationLoader->setConfigurationFilePath($path);
        }

        $this->add($this->createCommand($input, $output));

        return parent::doRun($input, $output);
    }

    protected function getDefaultCommands(): array
    {
        $commands = parent::getDefaultCommands();

        $commands[] = new DumpReferenceCommand($this->extensionManager);
        $commands[] = new DebugCommand($this, $this->configurationLoader, $this->extensionManager);

        return $commands;
    }

    /**
     * Configures container based on provided config file and profile.
     *
     * @param InputInterface $input
     *
     * @return array
     */
    private function loadConfiguration(InputInterface $input)
    {
        $profile = $input->getParameterOption(array('--profile', '-p')) ? : 'default';

        return $this->configurationLoader->loadConfiguration($profile);
    }

    /**
     * Creates main command for application.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return SymfonyCommand
     */
    private function createCommand(InputInterface $input, OutputInterface $output)
    {
        return $this->createContainer($input, $output)->get('cli.command');
    }

    /**
     * Creates container instance, loads extensions and freezes it.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return ContainerInterface
     */
    private function createContainer(InputInterface $input, OutputInterface $output)
    {
        $basePath = rtrim($this->getBasePath(), DIRECTORY_SEPARATOR);

        $container = new ContainerBuilder();

        $container->setParameter('cli.command.name', $this->getName());
        $container->setParameter('paths.base', $basePath);

        $container->set('cli.input', $input);
        $container->set('cli.output', $output);

        $extension = new ContainerLoader($this->extensionManager);
        $extension->load($container, $this->loadConfiguration($input));
        $container->addObjectResource($extension);
        $container->compile(true);

        return $container;
    }

    /**
     * Returns base path.
     *
     * @return string
     */
    private function getBasePath()
    {
        if ($configPath = $this->configurationLoader->getConfigurationFilePath()) {
            return realpath(dirname($configPath));
        }

        return realpath(getcwd());
    }

    /**
     * Gets the name of the command based on input.
     *
     * @param InputInterface $input The input interface
     *
     * @return string The command name
     */
    protected function getCommandName(InputInterface $input): string
    {
        if ($input->hasParameterOption(array('--config-reference'))) {
            return 'dump-reference';
        }

        if ($input->hasParameterOption(array('--debug'))) {
            return 'debug';
        }

        return $this->getName();
    }

    protected function configureIO(InputInterface $input, OutputInterface $output): void
    {
        if (true === $input->hasParameterOption(array('--colors'))) {
            $output->setDecorated(true);
        } elseif (true === $input->hasParameterOption(array('--no-colors'))) {
            $output->setDecorated(false);
        }

        parent::configureIO($input, $output);
    }
}

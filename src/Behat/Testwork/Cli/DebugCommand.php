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
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides debug information about the current environment.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class DebugCommand extends BaseCommand
{
    /**
     * @var Application
     */
    private $application;
    /**
     * @var ConfigurationLoader
     */
    private $configurationLoader;
    /**
     * @var ExtensionManager
     */
    private $extensionManager;

    /**
     * Initialises command.
     *
     * @param Application         $application
     * @param ConfigurationLoader $configurationLoader
     * @param ExtensionManager    $extensionManager
     */
    public function __construct(
        Application $application,
        ConfigurationLoader $configurationLoader,
        ExtensionManager $extensionManager
    ) {
        $this->application = $application;
        $this->configurationLoader = $configurationLoader;
        $this->extensionManager = $extensionManager;

        parent::__construct('debug');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('%s version %s', $this->application->getName(), $this->application->getVersion()));

        $output->writeln('');

        $debug = $this->configurationLoader->debugInformation();
        $output->writeln('--- configuration');
        $output->writeln(sprintf('    environment variable (%s): %s', $debug['environment_variable_name'], $debug['environment_variable_content']));
        $output->writeln(sprintf('    configuration file: %s', $debug['configuration_file_path']));

        $output->writeln('');

        $debug = $this->extensionManager->debugInformation();
        $output->writeln('--- extensions');
        $output->writeln(sprintf('    extensions loaded: %s', count($debug['extensions_list']) ? implode(', ', $debug['extensions_list']) : 'none'));

        $output->writeln('');

        return 0;
    }
}

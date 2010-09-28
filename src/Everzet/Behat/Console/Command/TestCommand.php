<?php

namespace Everzet\Behat\Console\Command;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Behat\Exception\Redundant;

/*
 * This file is part of the Behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat application test command.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TestCommand extends Command
{
    /**
     * @see Symfony\Component\Console\Command\Command
     */
    protected function configure()
    {
        $this->setName('test');

        // Set commands default parameters from container loaded ones
        $this->setDefinition(array(
            new InputArgument('features'
              , InputArgument::OPTIONAL
              , 'Features path'
            ),
            new InputOption('--configuration',  '-c'
              , InputOption::PARAMETER_REQUIRED
              , 'Specify configuration file (*.xml or *.yml)'
            ),
            new InputOption('--format',         '-f'
              , InputOption::PARAMETER_REQUIRED
              , 'Change output formatter'
            ),
            new InputOption('--tags',           '-t'
              , InputOption::PARAMETER_REQUIRED
              , 'Only executes features or scenarios with specified tags'
            ),
        ));
    }

    /**
     * @see Symfony\Component\Console\Command\Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Init container
        $container  = new ContainerBuilder();
        $xmlLoader  = new XmlFileLoader($container);
        $xmlLoader->load(__DIR__ . '/../../ServiceContainer/container.xml');

        // Set initial container services & parameters
        $container->set('output',               $output);
        $container->setParameter('dir.work',    $cwd = getcwd());
        $container->setParameter('dir.lib',     realpath(__DIR__ . '/../../../../../'));

        // Guess configuration file path
        if (null !== $input->getOption('configuration')) {
            $container->setParameter('configuration.path', $input->getOption('configuration'));
        } elseif (is_file($cwd . '/behat.yml')) {
            $container->setParameter('configuration.path', $cwd . '/behat.yml');
        } elseif (is_file($cwd . '/behat.xml')) {
            $container->setParameter('configuration.path', $cwd . '/behat.xml');
        }

        // Load external configuration
        if ($container->hasParameter('configuration.path')) {
            $container->
                getConfigurationLoaderService()->
                load($container->getParameter('configuration.path'));
        }

        // Read command arguments & options
        if (null !== $input->getArgument('features')) {
            $container->setParameter('features.path',     realpath($input->getArgument('features')));
        }
        if (null !== $input->getOption('format')) {
            $container->setParameter('formatter.name',    $input->getOption('format'));
        }
        if (null !== $input->getOption('tags')) {
            $container->setParameter('filter.tags',       $input->getOption('tags'));
        }
        if (null !== $input->getOption('verbose')) {
            $container->setParameter('formatter.verbose', $input->getOption('verbose'));
        }

        // Replace parameter tokens
        $container->
            getConfigurationLoaderService()->
            prepareContainerParameters();

        // Load hooks
        $container->
            getHooksLoaderService()->
            load($container->getParameter('hooks.file'));

        // Notify suite.run.before event
        $container->
            getEventDispatcherService()->
            notify(new Event($container, 'suite.run.before'));

        // Load steps
        try {
            $container->
                getStepsLoaderService()->
                load($container->getParameter('steps.path'));
        } catch (Redundant $e) {
            $output->write(sprintf("\033[31m%s\033[0m", strtr($e->getMessage(),
                array($container->getParameter('features.path') . '/' => '')
            )), true, 1);
            return 1;
        }

        // Load features runner
        $features = $container->
            getFeaturesLoaderService()->
            load($container->getParameter('features.files'));

        // Run features
        $result = 0;
        $timer  = microtime(true);
        foreach ($features as $feature) {
            $tester = $container->getFeatureTesterService();
            $result = max($result, $feature->accept($tester));
        }
        $timer  = microtime(true) - $timer;

        // Notify suite.run.after event
        $container->
            getEventDispatcherService()->
            notify(new Event($container, 'suite.run.after'));

        // Print run time
        $output->writeln(sprintf("%.3fs", $timer));

        // Return exit code
        return $result;
    }
}

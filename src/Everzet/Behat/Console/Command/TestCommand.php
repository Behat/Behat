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
use Symfony\Component\Finder\Finder;

use Everzet\Behat\Exception\Redundant;

/*
 * This file is part of the Behat.
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
     * @see     Symfony\Component\Console\Command\Command
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
            new InputOption('--no-color',       null
              , InputOption::PARAMETER_NONE
              , 'No colors in output'
            ),
            new InputOption('--i18n',           null
              , InputOption::PARAMETER_REQUIRED
              , 'Print formatters output in particular language'
            ),
        ));
    }

    /**
     * @see     Symfony\Component\Console\Command\Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Init container
        $container  = new ContainerBuilder();
        $xmlLoader  = new XmlFileLoader($container);
        $xmlLoader->load(__DIR__ . '/../../ServiceContainer/container.xml');

        // Set initial container services & parameters
        $container->set('behat.output',             $output);
        $container->setParameter('behat.work.path', $cwd = getcwd());
        $container->setParameter('behat.lib.path',  realpath(__DIR__ . '/../../../../../'));

        // Guess configuration file path
        if (null !== $input->getOption('configuration')) {
            $container->setParameter('behat.configuration.file', $input->getOption('configuration'));
        } elseif (is_file($cwd . '/behat.yml')) {
            $container->setParameter('behat.configuration.file', $cwd . '/behat.yml');
        } elseif (is_file($cwd . '/behat.xml')) {
            $container->setParameter('behat.configuration.file', $cwd . '/behat.xml');
        }

        // Load external configuration and set config path
        if ($container->hasParameter('behat.configuration.file')) {
            $container->setParameter('behat.configuration.path', dirname($container->getParameter('behat.configuration.file')));
            $container->
                getBehat_ConfigurationLoaderService()->
                load($container->getParameter('behat.configuration.file'));
        }

        // Read command arguments & options
        if (null !== $input->getArgument('features')) {
            $container->setParameter('behat.features.path',     realpath($input->getArgument('features')));
        }
        if (null !== $input->getOption('format')) {
            $container->setParameter('behat.formatter.name',    $input->getOption('format'));
        }
        if (null !== $input->getOption('tags')) {
            $container->setParameter('behat.filter.tags',       $input->getOption('tags'));
        }
        if (null !== $input->getOption('no-color')) {
            $container->setParameter('behat.formatter.colors',  !$input->getOption('no-color'));
        }
        if (null !== $input->getOption('verbose')) {
            $container->setParameter('behat.formatter.verbose', $input->getOption('verbose'));
        }
        if (null !== $input->getOption('i18n')) {
            $container->setParameter('behat.formatter.locale',  $input->getOption('i18n'));
        } else {
            $container->setParameter('behat.formatter.locale',  'en');
        }

        // Replace parameter tokens
        $container->
            getBehat_ConfigurationLoaderService()->
            prepareContainerParameters();

        // Load hooks
        $container->
            getBehat_HooksLoaderService()->
            load($container->getParameter('behat.hooks.file'));







        // Get feature files
        $featuresContainer  = $container->getBehat_FeaturesContainerService();
        $featuresPath       = $container->getParameter('behat.features.path');

        // Add features paths to container resources list
        foreach ($this->findFeatureResources($featuresPath) as $path) {
            $featuresContainer->addResource('gherkin', $path);
        }

        // Get definitions files
        $definitionsContainer   = $container->getBehat_DefinitionsContainerService();
        $stepsPaths             = $container->getParameter('behat.steps.path');

        foreach ((array) $stepsPaths as $stepsPath) {
            foreach ($this->findDefinitionResources($stepsPath) as $path) {
                $definitionsContainer->addResource('php', $path);
            }
        }

        // Notify suite.run.before event
        $container->getBehat_EventDispatcherService()->notify(new Event($container, 'suite.run.before'));
        $timer = microtime(true);

        // Run features
        $result = 0;
        foreach ($featuresContainer->getFeatures() as $feature) {
            $tester = $container->getBehat_FeatureTesterService();
            $result = max($result, $feature->accept($tester));
        }

        // Notify suite.run.after event
        $container->getBehat_EventDispatcherService()->notify(new Event($container, 'suite.run.after', array(
            'time'  => ($timer = microtime(true) - $timer)
        )));

        // Print run time
        $output->writeln(sprintf("%.3fs", $timer));

        // Return exit code
        return intval(0 < $result);
    }

    protected function findFeatureResources($featuresPath)
    {
        if (is_file($featuresPath)) {
            $paths  = array($featuresPath);
        } elseif (is_dir($featuresPath)) {
            $finder = new Finder();
            $paths  = $finder->files()->name('*.feature')->in($featuresPath);
        } else {
            throw new \InvalidArgumentException(sprintf('Provide correct feature(s) path. "%s" given', $featuresPath));
        }

        return $paths;
    }

    protected function findDefinitionResources($stepsPath)
    {
        $finder = new Finder();
        $paths  = $finder->files()->name('*.php')->in($stepsPath);

        return $paths;
    }
}


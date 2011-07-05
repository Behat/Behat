<?php

namespace Behat\Behat\Console\Command;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\Definition\DefinitionPrinter,
    Behat\Behat\Event\SuiteEvent,
    Behat\Behat\PathLocator;

use Behat\Gherkin\Keywords\KeywordsDumper;

use Behat\Behat\Console\Processor\FormatProcessor,
    Behat\Behat\Console\Processor\GherkinProcessor,
    Behat\Behat\Console\Processor\ContextProcessor,
    Behat\Behat\Console\Processor\LocatorProcessor,
    Behat\Behat\Console\Processor\InitProcessor,
    Behat\Behat\Console\Processor\ContainerProcessor,
    Behat\Behat\Console\Processor\HelpProcessor;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat console command.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BehatCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->formatProcessor = new FormatProcessor();
        $this->gherkinProcessor = new GherkinProcessor();
        $this->contextProcessor = new ContextProcessor();
        $this->locatorProcessor = new LocatorProcessor();
        $this->initProcessor = new InitProcessor();
        $this->containerProcessor = new ContainerProcessor();
        $this->helpProcessor = new HelpProcessor();

        $this->setName('behat');
        $this->setDefinition(array_merge(
            array(
                new InputArgument('features',
                    InputArgument::OPTIONAL,
                    'Feature(s) to run. Could be a dir (<comment>features/</comment>), ' .
                    'a feature (<comment>*.feature</comment>) or a scenario at specific line ' .
                    '(<comment>*.feature:10</comment>).'
                ),
            ),
            $this->initProcessor->getInputOptions(),
            $this->helpProcessor->getInputOptions(),
            $this->containerProcessor->getInputOptions(),
//            $this->locatorProcessor->getInputOptions(),
            $this->gherkinProcessor->getInputOptions(),
            $this->formatProcessor->getInputOptions(),
//            $this->contextProcessor->getInputOptions(),
            $this->getRunOptions()
        ));
    }

    /**
     * Returns array of run options for command.
     *
     * @return  array
     */
    protected function getRunOptions()
    {
        return array(
            new InputOption('--strict',         null,
                InputOption::VALUE_NONE,
                '       ' .
                'Fail if there are any undefined or pending steps.'
            ),
            new InputOption('--rerun',          null,
                InputOption::VALUE_REQUIRED,
                '        ' .
                'Save list of failed scenarios into file or use existing file to run only scenarios from it.'
            ),
        );
    }

    /**
     * {@inheritdoc}
     *
     * @uses    createContainer()
     * @uses    locateBasePath()
     * @uses    getContextClass()
     * @uses    createFormatter()
     * @uses    initFeaturesDirectoryStructure()
     * @uses    runFeatures()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = new ContainerBuilder();

        $this->containerProcessor->process($container, $input, $output);
        $this->locatorProcessor->process($container, $input, $output);
        $this->initProcessor->process($container, $input, $output);
        $this->contextProcessor->process($container, $input, $output);
        $this->formatProcessor->process($container, $input, $output);
        $this->helpProcessor->process($container, $input, $output);
        $this->gherkinProcessor->process($container, $input, $output);

        $locator = $container->get('behat.path_locator');

        // rerun data collector
        $rerunDataCollector = $container->get('behat.rerun_data_collector');
        if ($input->getOption('rerun') || $container->getParameter('behat.options.rerun')) {
            $rerunDataCollector->setRerunFile(
                $input->getOption('rerun') ?: $container->getParameter('behat.options.rerun')
            );
            $eventDispatcher->addSubscriber($rerunDataCollector, 0);
        }

        // run features
        $result = $this->runFeatures(
            $rerunDataCollector->hasFailedScenarios()
                ? $rerunDataCollector->getFailedScenariosPaths()
                : $locator->locateFeaturesPaths(),
            $container
        );
        if ($input->getOption('strict') || $container->getParameter('behat.options.strict')) {
            return intval(0 < $result);
        } else {
            return intval(4 === $result);
        }
    }

    /**
     * Runs specified features.
     *
     * @param   array                                                       $paths      features paths
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     *
     * @return  integer
     */
    protected function runFeatures(array $paths, ContainerInterface $container)
    {
        $result     = 0;
        $gherkin    = $container->get('gherkin');
        $dispatcher = $container->get('behat.event_dispatcher');
        $logger     = $container->get('behat.logger');

        $dispatcher->dispatch('beforeSuite', new SuiteEvent($logger, false));

        // catch app interruption
        if (function_exists('pcntl_signal')) {
            declare(ticks = 1);
            pcntl_signal(SIGINT, function() use($dispatcher, $logger) {
                $dispatcher->dispatch('afterSuite', new SuiteEvent($logger, false));
                exit(0);
            });
        }

        // read features from paths
        foreach ($paths as $path) {
            $features = $gherkin->load((string) $path);

            // and run them in FeatureTester
            foreach ($features as $feature) {
                $tester = $container->get('behat.tester.feature');
                $result = max($result, $feature->accept($tester));
            }
        }

        $dispatcher->dispatch('afterSuite', new SuiteEvent($logger, true));

        return $result;
    }
}

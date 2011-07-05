<?php

namespace Behat\Behat\Console\Command;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\Event\SuiteEvent,
    Behat\Behat\Console\Processor\FormatProcessor,
    Behat\Behat\Console\Processor\GherkinProcessor,
    Behat\Behat\Console\Processor\ContextProcessor,
    Behat\Behat\Console\Processor\LocatorProcessor,
    Behat\Behat\Console\Processor\InitProcessor,
    Behat\Behat\Console\Processor\ContainerProcessor,
    Behat\Behat\Console\Processor\HelpProcessor,
    Behat\Behat\Console\Processor\RerunProcessor;

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
        $this->rerunProcessor = new RerunProcessor();

        $this->setName('behat');
        $this->setDefinition(array_merge(
            array(
                new InputArgument('features',
                    InputArgument::OPTIONAL,
                    'Feature(s) to run. Could be a dir (<comment>features/</comment>), ' .
                    'a feature (<comment>*.feature</comment>) or a scenario at specific line ' .
                    '(<comment>*.feature:10</comment>).'
                ),
                new InputOption('--strict',         null,
                    InputOption::VALUE_NONE,
                    '       ' .
                    'Fail if there are any undefined or pending steps.'
                ),
            ),
            $this->initProcessor->getInputOptions(),
            $this->helpProcessor->getInputOptions(),
            $this->containerProcessor->getInputOptions(),
//            $this->locatorProcessor->getInputOptions(),
            $this->gherkinProcessor->getInputOptions(),
            $this->formatProcessor->getInputOptions(),
//            $this->contextProcessor->getInputOptions(),
            $this->rerunProcessor->getInputOptions()
        ));
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
        $this->rerunProcessor->process($container, $input, $output);

        $result     = 0;
        $gherkin    = $container->get('gherkin');
        $dispatcher = $container->get('behat.event_dispatcher');
        $logger     = $container->get('behat.logger');
        $paths      = $this->getFeaturesPaths($container);

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

        if ($input->getOption('strict') || $container->getParameter('behat.options.strict')) {
            return intval(0 < $result);
        } else {
            return intval(4 === $result);
        }
    }

    private function getFeaturesPaths(ContainerInterface $container)
    {
        $rerun = $container->get('behat.rerun_data_collector');
        if ($rerun->hasFailedScenarios()) {
            return $return->getFailedScenariosPaths();
        }

        return $container->get('behat.path_locator')->locateFeaturesPaths();
    }
}

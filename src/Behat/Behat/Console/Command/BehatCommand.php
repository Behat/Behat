<?php

namespace Behat\Behat\Console\Command;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\Event\SuiteEvent,
    Behat\Behat\Console\Processor;

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
class BehatCommand extends BaseCommand
{
    /**
     * Service container.
     *
     * @var     Symfony\Component\DependencyInjection\ContainerBuilder
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->container = new ContainerBuilder();

        $this->setProcessors(array(
            new Processor\ContainerProcessor(),
            new Processor\LocatorProcessor(),
            new Processor\InitProcessor(),
            new Processor\ContextProcessor(),
            new Processor\FormatProcessor(),
            new Processor\HelpProcessor(),
            new Processor\GherkinProcessor(),
            new Processor\RerunProcessor(),
        ));

        $this->setName('behat');
        $this->setDefinition(array_merge(
            array(
                new InputArgument('features',
                    InputArgument::OPTIONAL,
                    'Feature(s) to run. Could be a dir (<comment>features/</comment>), ' .
                    'a feature (<comment>*.feature</comment>) or a scenario at specific line ' .
                    '(<comment>*.feature:10</comment>).'
                )
            ),
            $this->getProcessorsInputOptions(),
            array(
                new InputOption('--strict',         null,
                    InputOption::VALUE_NONE,
                    '       ' .
                    'Fail if there are any undefined or pending steps.'
                )
            )
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * {@inheritdoc}
     *
     * @uses    getFeaturesPaths()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result     = 0;
        $gherkin    = $this->getContainer()->get('gherkin');
        $dispatcher = $this->getContainer()->get('behat.event_dispatcher');
        $logger     = $this->getContainer()->get('behat.logger');
        $paths      = $this->getContainer()->get('behat.rerun_data_collector')->locateFeaturesPaths();

        $dispatcher->dispatch('beforeSuite', new SuiteEvent($logger, false));

        // catch app interruption
        if (function_exists('pcntl_signal')) {
            declare(ticks = 1);
            pcntl_signal(SIGINT, function() use($dispatcher, $logger) {
                $dispatcher->dispatch('afterSuite', new SuiteEvent($logger, false));
                exit(1);
            });
        }

        // read features from paths
        foreach ($paths as $path) {
            $features = $gherkin->load((string) $path);

            // and run them in FeatureTester
            foreach ($features as $feature) {
                $tester = $this->getContainer()->get('behat.tester.feature');
                $result = max($result, $feature->accept($tester));
            }
        }

        $dispatcher->dispatch('afterSuite', new SuiteEvent($logger, true));

        if ($input->getOption('strict') || $this->getContainer()->getParameter('behat.options.strict')) {
            return intval(0 < $result);
        } else {
            return intval(4 === $result);
        }
    }
}

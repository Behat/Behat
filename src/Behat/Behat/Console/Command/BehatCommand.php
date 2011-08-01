<?php

namespace Behat\Behat\Console\Command;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\Console\Processor,
    Behat\Behat\Event\SuiteEvent;

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

        $this
            ->setName('behat')
            ->setProcessors(array(
                new Processor\ContainerProcessor(),
                new Processor\LocatorProcessor(),
                new Processor\InitProcessor(),
                new Processor\ContextProcessor(),
                new Processor\FormatProcessor(),
                new Processor\HelpProcessor(),
                new Processor\GherkinProcessor(),
                new Processor\RerunProcessor(),
            ))
            ->addArgument('features', InputArgument::OPTIONAL,
                "Feature(s) to run. Could be:\n" .
                "- a dir (<comment>features/</comment>)\n" .
                "- a feature (<comment>*.feature</comment>)\n" .
                "- a scenario at specific line (<comment>*.feature:10</comment>)."
            )
            ->configureProcessors()
            ->addOption('--strict', null, InputOption::VALUE_NONE,
                'Fail if there are any undefined or pending steps.'
            )
        ;
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
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $paths   = $this->getContainer()->get('behat.rerun_data_collector')->locateFeaturesPaths();
        $gherkin = $this->getContainer()->get('gherkin');

        $this->startSuite();

        // read all features from their paths
        foreach ($paths as $path) {
            // parse every feature with Gherkin
            $features = $gherkin->load((string) $path);

            // and run it in FeatureTester
            foreach ($features as $feature) {
                $feature->accept($this->getContainer()->get('behat.tester.feature'));
            }
        }

        return $this->finishSuite($input);
    }

    /**
     * Starts suite.
     */
    protected function startSuite()
    {
        $dispatcher = $this->getContainer()->get('behat.event_dispatcher');
        $logger     = $this->getContainer()->get('behat.logger');
        $parameters = $this->getContainer()->getParameter('behat.context.parameters');

        $dispatcher->dispatch('beforeSuite', new SuiteEvent($logger, $parameters, false));

        // catch app interruption
        if (function_exists('pcntl_signal')) {
            declare(ticks = 1);
            pcntl_signal(SIGINT, function() use($dispatcher, $parameters, $logger) {
                $dispatcher->dispatch('afterSuite', new SuiteEvent($logger, $parameters, false));
                exit(1);
            });
        }

    }

    /**
     * Finishes suite and returns suite run result.
     *
     * @return  integer result code
     */
    protected function finishSuite($input)
    {
        $dispatcher = $this->getContainer()->get('behat.event_dispatcher');
        $logger     = $this->getContainer()->get('behat.logger');
        $parameters = $this->getContainer()->getParameter('behat.context.parameters');

        $dispatcher->dispatch('afterSuite', new SuiteEvent($logger, $parameters, true));

        if ($input->getOption('strict') || $this->getContainer()->getParameter('behat.options.strict')) {
            return intval(0 < $logger->getSuiteResult());
        } else {
            return intval(4 === $logger->getSuiteResult());
        }

    }
}

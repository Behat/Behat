<?php

namespace Behat\Behat\Console\Command;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Gherkin\Gherkin;

use Behat\Behat\Console\Input\InputDefinition,
    Behat\Behat\Console\Processor\ProcessorInterface,
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
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BehatCommand extends BaseCommand
{
    private $container;
    private $featuresPaths;
    private $strict = true;
    private $dryRun = false;

    /**
     * Initializes command.
     *
     * @param ContainerInterface $container
     * @param ProcessorInterface $processor
     */
    public function __construct(ContainerInterface $container, ProcessorInterface $processor)
    {
        parent::__construct('behat');

        $this->container = $container;
        $this->setDefinition(new InputDefinition);
        $this->setProcessor($processor);
    }

    /**
     * Sets features/scenarios paths to run.
     *
     * @param array $paths
     */
    public function setFeaturesPaths(array $paths)
    {
        $this->featuresPaths = $paths;
    }

    /**
     * Returns paths to the features of current suite (basepath).
     *
     * @return array
     */
    public function getFeaturesPaths()
    {
        return $this->featuresPaths;
    }

    /**
     * Sets runner to be strict.
     *
     * @param Boolean $strict
     */
    public function setStrict($strict = true)
    {
        $this->strict = (bool) $strict;
    }

    /**
     * Checks whether runner is strict.
     *
     * @return Boolean
     */
    public function isStrict()
    {
        return $this->strict;
    }

    /**
     * Sets suite to dry-run mode (skip all steps).
     *
     * @param Boolean $dryRun
     */
    public function setDryRun($dryRun = true)
    {
        $this->dryRun = (bool) $dryRun;
    }

    /**
     * Checks whether runner is in dry-run mode.
     *
     * @return Boolean
     */
    public function isDryRun()
    {
        return $this->dryRun;
    }

    /**
     * Returns container instance.
     *
     * @return ContainerInterface
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
        $gherkin = $this->getContainer()->get('gherkin');

        $this->beforeSuite();
        $this->runFeatures($gherkin);
        $this->afterSuite();

        return $this->getCliReturnCode();
    }

    /**
     * Parses and runs provided features.
     *
     * @param Gherkin $gherkin gherkin parser/loader
     */
    protected function runFeatures(Gherkin $gherkin)
    {
        foreach ($this->getFeaturesPaths() as $path) {
            // parse every feature with Gherkin
            $features = $gherkin->load((string) $path);

            // and run it in FeatureTester
            foreach ($features as $feature) {
                $tester = $this->getContainer()->get('behat.tester.feature');
                $tester->setSkip($this->isDryRun());

                $feature->accept($tester);
            }
        }
    }

    /**
     * Returns CLI code for finished suite.
     *
     * @return integer
     */
    protected function getCliReturnCode()
    {
        $logger = $this->getContainer()->get('behat.logger');

        if ($this->isStrict()) {
            return intval(0 < $logger->getSuiteResult());
        }

        return intval(4 === $logger->getSuiteResult());
    }

    /**
     * Abort Suite on interuption or when running in stop-on-failure mode.
     */
    public function abortSuite()
    {
        $dispatcher = $this->getContainer()->get('behat.event_dispatcher');
        $logger     = $this->getContainer()->get('behat.logger');
        $parameters = $this->getContainer()->get('behat.context.dispatcher')->getContextParameters();

        $dispatcher->dispatch('afterSuite', new SuiteEvent($logger, $parameters, false));
        exit(1);
    }

    /**
     * Fire beforeSuite event.
     */
    protected function beforeSuite()
    {
        $dispatcher = $this->getContainer()->get('behat.event_dispatcher');
        $logger     = $this->getContainer()->get('behat.logger');
        $parameters = $this->getContainer()->get('behat.context.dispatcher')->getContextParameters();

        $dispatcher->dispatch('beforeSuite', new SuiteEvent($logger, $parameters, false));

        // catch app interruption
        if (function_exists('pcntl_signal')) {
            declare(ticks = 1);
            pcntl_signal(SIGINT, array($this, 'abortSuite'));
        }
    }

    /**
     * Fire afterSuite event.
     */
    protected function afterSuite()
    {
        $dispatcher = $this->getContainer()->get('behat.event_dispatcher');
        $logger     = $this->getContainer()->get('behat.logger');
        $parameters = $this->getContainer()->get('behat.context.dispatcher')->getContextParameters();

        $dispatcher->dispatch('afterSuite', new SuiteEvent($logger, $parameters, true));
    }
}

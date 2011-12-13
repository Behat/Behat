<?php

namespace Behat\Behat;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Behat\Behat\Event\SuiteEvent;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat suite runner.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Runner
{
    private $container;

    private $strict = true;

    /**
     * Initializes runner.
     *
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Sets runner to be strict.
     *
     * @param   Boolean $strict
     */
    public function setStrict($strict = true)
    {
        $this->strict = (bool) $strict;
    }

    /**
     * Checks whether runner is strict.
     *
     * @return  Boolean
     */
    public function isStrict()
    {
        return $this->strict;
    }

    /**
     * Runs feature suite.
     *
     * @return  integer CLI return code
     */
    public function run()
    {
        $paths   = $this->container->get('behat.rerun_data_collector')->locateFeaturesPaths();
        $gherkin = $this->container->get('gherkin');

        $this->beforeSuite();

        // read all features from their paths
        foreach ($paths as $path) {
            // parse every feature with Gherkin
            $features = $gherkin->load((string) $path);
            $tester   = $this->container->get('behat.tester.feature');

            // and run it in FeatureTester
            foreach ($features as $feature) {
                $feature->accept($tester);
            }
        }

        $this->afterSuite();

        return $this->getCliReturnCode();
    }

    /**
     * Returns CLI code for finished suite.
     *
     * @return  integer
     */
    protected function getCliReturnCode()
    {
        $logger = $this->container->get('behat.logger');

        if ($this->isStrict()) {
            return intval(0 < $logger->getSuiteResult());
        }

        return intval(4 === $logger->getSuiteResult());
    }

    /**
     * Fire beforeSuite event.
     */
    protected function beforeSuite()
    {
        $dispatcher = $this->container->get('behat.event_dispatcher');
        $logger     = $this->container->get('behat.logger');
        $parameters = $this->container->get('behat.context_dispatcher')->getContextParameters();

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
     * Fire afterSuite event.
     */
    protected function afterSuite()
    {
        $dispatcher = $this->container->get('behat.event_dispatcher');
        $logger     = $this->container->get('behat.logger');
        $parameters = $this->container->get('behat.context_dispatcher')->getContextParameters();

        $dispatcher->dispatch('afterSuite', new SuiteEvent($logger, $parameters, true));
    }
}

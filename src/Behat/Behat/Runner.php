<?php

namespace Behat\Behat;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Behat\Behat\Event\SuiteEvent;

use Behat\Gherkin\Gherkin;

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
    private $featuresPaths;
    private $strict = true;
    private $dryRun = false;

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
     * Returns container associated with this runner.
     *
     * @return  Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Sets main context class.
     *
     * @param   string  $class
     */
    public function setMainContextClass($class)
    {
        $dispatcher = $this->container->get('behat.context_dispatcher');
        $reader     = $this->container->get('behat.context_reader');

        if (null !== $dispatcher->getContextClass()) {
            $this->cleanContextInformation();
        }

        $dispatcher->setContextClass($class);
        $reader->read();
    }

    /**
     * Cleans context information (definitions, transformations, hooks).
     */
    public function cleanContextInformation()
    {
        $this->container->get('behat.definition_dispatcher')->removeDefinitions();
        $this->container->get('behat.definition_dispatcher')->removeTransformations();
        $this->container->get('behat.hook_dispatcher')->removeHooks();
    }

    /**
     * Sets path locator base path.
     *
     * @param   string  $path
     */
    public function setLocatorBasePath($path)
    {
        $this->container->get('behat.path_locator')->locateBasePath($path);
    }

    /**
     * Sets features/scenarios paths to run.
     *
     * @param   array   $paths
     */
    public function setFeaturesPaths(array $paths)
    {
        $this->featuresPaths = $paths;
    }

    /**
     * Returns paths to the features of current suite (basepath).
     *
     * @return  array
     */
    public function getFeaturesPaths()
    {
        return $this->featuresPaths
            ?: $this->container->get('behat.path_locator')->locateFeaturesPaths();
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
     * Sets suite to dry-run mode (skip all steps).
     *
     * @param   Boolean $dryRun
     */
    public function setDryRun($dryRun = true)
    {
        $this->dryRun = (bool) $dryRun;
    }

    /**
     * Checks whether runner is in dry-run mode.
     *
     * @return  Boolean
     */
    public function isDryRun()
    {
        return $this->dryRun;
    }

    /**
     * Runs feature suite.
     *
     * @return  integer CLI return code
     */
    public function runSuite()
    {
        $gherkin = $this->container->get('gherkin');

        $this->beforeSuite();
        $this->runFeatures($gherkin, $this->getFeaturesPaths());
        $this->afterSuite();

        return $this->getCliReturnCode();
    }

    /**
     * Parses and runs provided features.
     *
     * @param   Behat\Gherkin\Gherkin   $gherkin    gherkin parser/loader
     * @param   array                   $features   list of feature files
     */
    protected function runFeatures(Gherkin $gherkin, $features)
    {
        foreach ($features as $path) {
            // parse every feature with Gherkin
            $features = $gherkin->load((string) $path);

            // and run it in FeatureTester
            foreach ($features as $feature) {
                $tester = $this->container->get('behat.tester.feature');
                $tester->setDryRun($this->isDryRun());

                $feature->accept($tester);
            }
        }
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

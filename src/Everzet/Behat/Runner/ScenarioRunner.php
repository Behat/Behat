<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\DependencyInjection\Container;

use Everzet\Gherkin\Element\Scenario\ScenarioElement;
use Everzet\Gherkin\Element\Scenario\BackgroundElement;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Scenario runner.
 * Runs scenario steps runners.
 *
 * @package     Behat
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ScenarioRunner extends BaseRunner implements RunnerInterface
{
    protected $scenario;
    protected $definitions;

    protected $backgroundRunner;
    protected $tokens = array();
    protected $skip = false;

    /**
     * Creates runner instance
     *
     * @param   ScenarioElement     $scenario   scenario element
     * @param   BackgroundElement   $background background element
     * @param   Container           $container  dependency container
     * @param   RunnerInterface     $parent     parent runner
     */
    public function __construct(ScenarioElement $scenario, BackgroundElement $background = null, 
                                Container $container, RunnerInterface $parent)
    {
        $this->scenario     = $scenario;
        $this->definitions  = $container->getStepsLoaderService();

        if (null !== $background) {
            $this->backgroundRunner = new BackgroundRunner(
                $background
              , $this->definitions
              , $container
              , $this
            );
        }

        foreach ($scenario->getSteps() as $step) {
            $this->addChildRunner(new StepRunner($step, $this->definitions, $container, $this));
        }

        parent::__construct('scenario', $container->getEventDispatcherService(), $parent);
    }

    /**
     * Sets example tokens for steps (from Outline)
     *
     * @param   array   $tokens associative array of tokens
     */
    public function setTokens(array $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * @see Everzet\Behat\Runner\BaseRunner
     */
    public function getScenariosCount()
    {
        return 1;
    }

    /**
     * @see Everzet\Behat\Runner\BaseRunner
     */
    public function getStepsCount()
    {
        $count = parent::getStepsCount();

        if ($this->backgroundRunner) {
            $count += $this->backgroundRunner->getStepsCount();
        }

        return $count;
    }

    /**
     * @see Everzet\Behat\Runner\BaseRunner
     */
    public function getScenariosStatusesCount()
    {
        return array($this->getStatus() => 1);
    }

    /**
     * @see Everzet\Behat\Runner\BaseRunner
     */
    public function getStepsStatusesCount()
    {
        $statuses = parent::getStepsStatusesCount();

        if ($this->backgroundRunner) {
            foreach ($this->backgroundRunner->getStepsStatusesCount() as $status => $count) {
                if (!isset($statuses[$status])) {
                    $statuses[$status] = 0;
                }

                $statuses[$status] += $count;
            }
        }

        return $statuses;
    }

    /**
     * @see Everzet\Behat\Runner\BaseRunner
     */
    public function getDefinitionSnippets()
    {
        $snippets = parent::getDefinitionSnippets();

        if ($this->backgroundRunner) {
            $snippets = array_merge($snippets, $this->backgroundRunner->getDefinitionSnippets());
        }

        return $snippets;
    }

    /**
     * @see Everzet\Behat\Runner\BaseRunner
     */
    public function getFailedStepRunners()
    {
        $runners = parent::getFailedStepRunners();

        if ($this->backgroundRunner) {
            $runners = array_merge($runners, $this->backgroundRunner->getFailedStepRunners());
        }

        return $runners;
    }

    /**
     * @see Everzet\Behat\Runner\BaseRunner
     */
    public function getPendingStepRunners()
    {
        $runners = parent::getPendingStepRunners();

        if ($this->backgroundRunner) {
            $runners = array_merge($runners, $this->backgroundRunner->getPendingStepRunners());
        }

        return $runners;
    }

    /**
     * Returns scenario element
     *
     * @return  ScenarioElement
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    /**
     * Checks if this scenario is part of Scenario Outline
     *
     * @return  boolean if this scenario is runned inside Outline
     */
    public function isInOutline()
    {
        return $this->getParentRunner() instanceof ScenarioOutlineRunner;
    }

    /**
     * @see Everzet\Behat\Runner\BaseRunner
     */
    protected function doRun()
    {
        $status = $this->statusToCode('passed');

        if (null !== $this->backgroundRunner) {
            $code = $this->backgroundRunner->run();
            $status = max($status, $code);
            $this->skip = $this->backgroundRunner->isSkipped();
        }

        foreach ($this as $runner) {
            if (null !== $this->tokens && count($this->tokens)) {
                $runner->setTokens($this->tokens);
            }

            if (!$this->skip) {
                $code = $runner->run();
                if ($this->statusToCode('passed') !== $code) {
                    $this->skip = true;
                }
                $status = max($status, $code);
            } else {
                $code = $runner->skip();
                $status = max($status, $code);
            }
        }

        return $status;
    }
}

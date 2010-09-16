<?php

namespace Everzet\Behat\Runner;

use Symfony\Component\DependencyInjection\Container;

use Everzet\Gherkin\Element\Scenario\ScenarioOutlineElement;
use Everzet\Gherkin\Element\Scenario\BackgroundElement;

/*
 * This file is part of the behat package.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Scenario outline runner.
 * Runs 'exampled' scenarios.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ScenarioOutlineRunner extends BaseRunner implements RunnerInterface, \Iterator
{
    protected $outline;
    protected $background;

    /**
     * Creates runner instance
     *
     * @param   ScenarioOutlineElement  $outline    outline element
     * @param   BackgroundElement       $background background element
     * @param   Container               $container  dependency container
     * @param   RunnerInterface         $parent     parent runner
     */
    public function __construct(ScenarioOutlineElement $outline,
                                BackgroundElement $background = null, Container $container, 
                                RunnerInterface $parent)
    {
        $this->outline      = $outline;
        $this->background   = $background;

        foreach ($outline->getExamples()->getTable()->getHash() as $tokens) {
            $runner = new ScenarioRunner($outline, $background, $container, $this);
            $runner->setTokens($tokens);
            $this->addChildRunner($runner);
        }

        parent::__construct('scenario_outline', $container->getEventDispatcherService(), $parent);
    }

    /**
     * Returns outline element
     *
     * @return  ScenarioOutlineElement
     */
    public function getScenarioOutline()
    {
        return $this->outline;
    }

    /**
     * @see Everzet\Behat\Runner\BaseRunner
     */
    protected function doRun()
    {
        $status = $this->statusToCode('passed');

        foreach ($this as $runner) {
            $status = max($status, $runner->run());
        }

        return $status;
    }
}

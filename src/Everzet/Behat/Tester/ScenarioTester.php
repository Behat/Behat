<?php

namespace Everzet\Behat\Tester;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Gherkin\Node\NodeVisitorInterface;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Scenario Tester.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ScenarioTester implements NodeVisitorInterface
{
    protected $container;
    protected $dispatcher;
    protected $environment;

    /**
     * Initialize tester.
     *
     * @param   Container   $container  injection container
     */
    public function __construct(Container $container)
    {
        $this->container    = $container;
        $this->dispatcher   = $container->get('behat.event_dispatcher');
        $this->environment  = $container->get('behat.environment');
    }

    /**
     * Visit ScenarioNode & run tests against it.
     *
     * @param   Everzet\Gherkin\Node\ScenarioNode       $scenario       scenario node
     * 
     * @return  integer                                                 result
     */
    public function visit($scenario)
    {
        $this->dispatcher->notify(new Event($scenario, 'scenario.run.before'));

        $result = 0;
        $skip   = false;

        // Visit & test background if has one
        if ($scenario->getFeature()->hasBackground()) {
            $tester = $this->container->get('behat.background_tester');
            $tester->setEnvironment($this->environment);

            $bgResult = $scenario->getFeature()->getBackground()->accept($tester);

            if (0 !== $bgResult) {
                $skip = true;
            }
            $result = max($result, $bgResult);
        }

        // Visit & test steps
        foreach ($scenario->getSteps() as $step) {
            $tester = $this->container->get('behat.step_tester');
            $tester->setEnvironment($this->environment);
            $tester->skip($skip);

            $stResult = $step->accept($tester);

            if (0 !== $stResult) {
                $skip = true;
            }
            $result = max($result, $stResult);
        }

        $this->dispatcher->notify(new Event($scenario, 'scenario.run.after', array(
            'result'    => $result
          , 'skipped'   => $skip
        )));

        return $result;
    }
}

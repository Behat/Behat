<?php

namespace Everzet\Behat\Tester;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;

use Everzet\Gherkin\Node\NodeVisitorInterface;
use Everzet\Gherkin\Node\ScenarioNode;
use Everzet\Gherkin\Node\OutlineNode;

use Everzet\Behat\Exception\BehaviorException;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Feature Tester.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeatureTester implements NodeVisitorInterface
{
    protected $container;
    protected $dispatcher;

    /**
     * Initialize tester.
     *
     * @param   Container   $container  injection container
     */
    public function __construct(Container $container)
    {
        $this->container    = $container;
        $this->dispatcher   = $container->getBehat_EventDispatcherService();
    }

    /**
     * Visit FeatureNode & run tests against it.
     *
     * @param   Everzet\Gherkin\Node\FeatureNode        $feature        feature node
     * 
     * @return  integer                                                 result
     */
    public function visit($feature)
    {
        $this->dispatcher->notify(new Event($feature, 'feature.run.before'));

        $result = 0;

        // Filter scenarios
        $event = new Event($this, 'feature.run.filter_scenarios');
        $this->dispatcher->filter($event, $feature->getScenarios());

        // Test filtered scenarios
        foreach ($event->getReturnValue() as $scenario) {
            if ($scenario instanceof OutlineNode) {
                $tester = $this->container->getBehat_OutlineTesterService();
            } elseif ($scenario instanceof ScenarioNode) {    
                $tester = $this->container->getBehat_ScenarioTesterService();
            } else {
                throw new BehaviorException(
                    'Unknown scenario type found: ' . get_class($scenario)
                );
            }
            $result = max($result, $scenario->accept($tester));
        }

        $this->dispatcher->notify(new Event($feature, 'feature.run.after', array(
            'result' => $result
        )));

        return $result;
    }
}

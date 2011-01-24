<?php

namespace Everzet\Behat\Tester;

use Symfony\Component\DependencyInjection\Container,
    Symfony\Component\EventDispatcher\Event;

use Behat\Gherkin\Node\NodeVisitorInterface,
    Behat\Gherkin\Node\AbstractNode,
    Behat\Gherkin\Node\ScenarioNode,
    Behat\Gherkin\Node\OutlineNode;

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
        $this->dispatcher   = $container->get('behat.event_dispatcher');
    }

    /**
     * Visit FeatureNode & run tests against it.
     *
     * @param   AbstractNode    $feature        feature node
     * 
     * @return  integer                         result
     */
    public function visit(AbstractNode $feature)
    {
        $result = 0;

        // If feature has scenario - run them
        if (count($scenarios = $feature->getScenarios())) {
            $this->dispatcher->notify(new Event($feature, 'feature.run.before'));

            foreach ($scenarios as $scenario) {
                if ($scenario instanceof OutlineNode) {
                    $tester = $this->container->get('behat.outline_tester');
                } elseif ($scenario instanceof ScenarioNode) {    
                    $tester = $this->container->get('behat.scenario_tester');
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
        }

        return $result;
    }
}

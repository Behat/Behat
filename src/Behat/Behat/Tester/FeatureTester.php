<?php

namespace Behat\Behat\Tester;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\EventDispatcher\Event;

use Behat\Gherkin\Node\NodeVisitorInterface,
    Behat\Gherkin\Node\AbstractNode,
    Behat\Gherkin\Node\ScenarioNode,
    Behat\Gherkin\Node\OutlineNode;

use Behat\Behat\Exception\BehaviorException;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Feature tester.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeatureTester implements NodeVisitorInterface
{
    /**
     * Service container.
     *
     * @var     Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;
    /**
     * Event dispatcher.
     *
     * @var     Behat\Behat\EventDispatcher\EventDispatcher
     */
    protected $dispatcher;

    /**
     * Initializes tester.
     *
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container    = $container;
        $this->dispatcher   = $container->get('behat.event_dispatcher');
    }

    /**
     * Visits & tests FeatureNode.
     *
     * @param   Behat\Gherkin\Node\AbstractNode $feature
     *
     * @return  integer
     *
     * @throws  BehaviorException   if unknown scenario type (neither Outline or Scenario) found in feature
     */
    public function visit(AbstractNode $feature)
    {
        $result = 0;

        // If feature has scenario - run them
        if (count($scenarios = $feature->getScenarios())) {
            $this->dispatcher->notify(new Event($feature, 'feature.before'));

            foreach ($scenarios as $scenario) {
                if ($scenario instanceof OutlineNode) {
                    $tester = $this->container->get('behat.tester.outline');
                } elseif ($scenario instanceof ScenarioNode) {
                    $tester = $this->container->get('behat.tester.scenario');
                } else {
                    throw new BehaviorException(
                        'Unknown scenario type found: ' . get_class($scenario)
                    );
                }
                $result = max($result, $scenario->accept($tester));
            }

            $this->dispatcher->notify(new Event($feature, 'feature.after', array(
                'result' => $result
            )));
        }

        return $result;
    }
}

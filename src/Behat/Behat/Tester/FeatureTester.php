<?php

namespace Behat\Behat\Tester;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Behat\Gherkin\Node\NodeVisitorInterface,
    Behat\Gherkin\Node\AbstractNode,
    Behat\Gherkin\Node\ScenarioNode,
    Behat\Gherkin\Node\OutlineNode;

use Behat\Behat\Exception\BehaviorException,
    Behat\Behat\Event\FeatureEvent;

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
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeatureTester implements NodeVisitorInterface
{
    private $container;
    private $dispatcher;
    private $parameters;
    private $dryRun = false;

    /**
     * Initializes tester.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container  = $container;
        $this->dispatcher = $container->get('behat.event_dispatcher');
        $this->parameters = $container->get('behat.context.dispatcher')->getContextParameters();
    }

    /**
     * Sets tester to dry-run mode.
     *
     * @param Boolean $dryRun
     */
    public function setDryRun($dryRun = true)
    {
        $this->dryRun = (bool) $dryRun;
    }

    /**
     * Visits & tests FeatureNode.
     *
     * @param AbstractNode $feature
     *
     * @return integer
     *
     * @throws BehaviorException if unknown scenario type (neither Outline or Scenario) found in feature
     */
    public function visit(AbstractNode $feature)
    {
        $result = 0;

        // If feature has scenarios - run them
        if ($feature->hasScenarios()) {
            $this->dispatcher->dispatch(
                'beforeFeature', new FeatureEvent($feature, $this->parameters)
            );

            foreach ($feature->getScenarios() as $scenario) {
                if ($scenario instanceof OutlineNode) {
                    $tester = $this->container->get('behat.tester.outline');
                } elseif ($scenario instanceof ScenarioNode) {
                    $tester = $this->container->get('behat.tester.scenario');
                } else {
                    throw new BehaviorException(
                        'Unknown scenario type found: ' . get_class($scenario)
                    );
                }

                $tester->setDryRun($this->dryRun);
                $result = max($result, $scenario->accept($tester));
            }

            $this->dispatcher->dispatch(
                'afterFeature', new FeatureEvent($feature, $this->parameters, $result)
            );
        }

        return $result;
    }
}

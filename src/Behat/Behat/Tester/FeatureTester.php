<?php

namespace Behat\Behat\Tester;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Behat\Gherkin\Node\NodeVisitorInterface,
    Behat\Gherkin\Node\AbstractNode,
    Behat\Gherkin\Node\ScenarioNode,
    Behat\Gherkin\Node\OutlineNode;

use Behat\Behat\Exception\BehaviorException,
    Behat\Behat\Event\FeatureEvent,
    Behat\Behat\Event\StepEvent;

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
    private $container;
    /**
     * Event dispatcher.
     *
     * @var     Behat\Behat\EventDispatcher\EventDispatcher
     */
    private $dispatcher;
    /**
     * Context parameters.
     *
     * @var     mixed
     */
    private $parameters;
    /**
     * Dry run of tester.
     *
     * @var     Boolean
     */
    private $dryRun = false;
    /**
     * Count of retry attempts for the tester.
     *
     * @var     integer
     */
    private $allowedRetryAttempts = 0;
    /**
     * Current retry attempt count.
     *
     * @var     integer
     */
    private $retryAttempt = 0;

    /**
     * Initializes tester.
     *
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container  = $container;
        $this->dispatcher = $container->get('behat.event_dispatcher');
        $this->parameters = $container->get('behat.context_dispatcher')->getContextParameters();
    }

    /**
     * Sets tester to dry-run mode.
     *
     * @param   Boolean $dryRun
     */
    public function setDryRun($dryRun = true)
    {
        $this->dryRun = (bool) $dryRun;
    }

    /**
     * Set count of retry attempts for the tester.
     *
     * @param   integer $count
     */
    public function setAllowedRetryAttempts($count)
    {
        $this->allowedRetryAttempts = $count;
    }

    /**
     * Check wheter there are retry attempts left.
     *
     * @return  Boolean
     */
    public function isAttemptsLeft()
    {
        return $this->retryAttempt < $this->allowedRetryAttempts;
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
        $this->retryAttempt = 0;

        // If feature has scenarios - run them
        if ($feature->hasScenarios()) {
            $this->dispatcher->dispatch(
                'beforeFeature', new FeatureEvent($feature, $this->parameters)
            );

            $scenarioIterator = new \ArrayIterator($feature->getScenarios());
            while ($scenarioIterator->valid()) {
                $scenario = $scenarioIterator->current();
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
                $tester->setAllowInstability($this->isAttemptsLeft());
                $scResult = $scenario->accept($tester);
                $result = max($result, $scResult);

                if (StepEvent::UNSTABLE !== $scResult) {
                    $scenarioIterator->next();
                }
                $this->retryAttempt++;
            }

            $this->dispatcher->dispatch(
                'afterFeature', new FeatureEvent($feature, $this->parameters, $result)
            );
        }

        return $result;
    }
}

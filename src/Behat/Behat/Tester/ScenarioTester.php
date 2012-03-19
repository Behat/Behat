<?php

namespace Behat\Behat\Tester;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;

use Behat\Gherkin\Node\NodeVisitorInterface,
    Behat\Gherkin\Node\AbstractNode,
    Behat\Gherkin\Node\BackgroundNode,
    Behat\Gherkin\Node\StepNode;

use Behat\Behat\Context\ContextInterface,
    Behat\Behat\Event\ScenarioEvent,
    Behat\Behat\Event\StepEvent;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
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
     * Context.
     *
     * @var     Behat\Behat\Context\ContextInterface
     */
    private $context;
    /**
     * Dry run of scenario.
     *
     * @var     Boolean
     */
    private $dryRun = false;
    /**
     * Allow step instability instead of immidiate fail.
     *
     * @var     boolean
     */
    private $allowInstability = false;
    /**
     * Is test marked as unstable.
     *
     * @var     boolean
     */
    private $unstable = false;

    /**
     * Initializes tester.
     *
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container  = $container;
        $this->dispatcher = $container->get('behat.event_dispatcher');
        $this->context    = $container->get('behat.context_dispatcher')->createContext();
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
     * Set wheter the step will allow instability instead of
     * immidiate failure.
     *
     * @param   boolean $allowInstability
     */
    public function setAllowInstability($allowInstability)
    {
        $this->allowInstability = $allowInstability;
    }

    /**
     * Mark test as unstable.
     *
     * @param   boolean $unstable
     */
    public function unstable($unstable = true)
    {
        $this->unstable = $unstable;
    }

    /**
     * Visits & tests ScenarioNode.
     *
     * @param   Behat\Gherkin\Node\AbstractNode $scenario
     *
     * @return  integer
     */
    public function visit(AbstractNode $scenario)
    {
        $this->dispatcher->dispatch('beforeScenario', new ScenarioEvent($scenario, $this->context));

        $result = 0;
        $skip   = false;

        // Visit & test background if has one
        if ($scenario->getFeature()->hasBackground()) {
            $bgResult = $this->visitBackground($scenario->getFeature()->getBackground(), $this->context);
            if (StepEvent::UNSTABLE < $bgResult) {
                $skip = true;
            }
            if (StepEvent::UNSTABLE === $bgResult) {
                $this->unstable();
            }
            $result = max($result, $bgResult);
        }

        // Visit & test steps
        foreach ($scenario->getSteps() as $step) {
            $stResult = $this->visitStep($step, $this->context, array(), $skip);
            if (StepEvent::UNSTABLE < $stResult) {
                $skip = true;
            }
            if (StepEvent::UNSTABLE === $stResult) {
                $this->unstable();
            }
            $result = max($result, $stResult);
        }

        $this->dispatcher->dispatch('afterScenario', new ScenarioEvent(
            $scenario, $this->context, $result, $skip
        ));

        return $result;
    }

    /**
     * Visits & tests BackgroundNode.
     *
     * @param   Behat\Gherkin\Node\BackgroundNode       $background
     * @param   Behat\Behat\Context\ContextInterface    $context
     *
     * @uses    Behat\Behat\Tester\BackgroundTester::visit()
     *
     * @return  integer
     */
    protected function visitBackground(BackgroundNode $background, ContextInterface $context)
    {
        $tester = $this->container->get('behat.tester.background');
        $tester->setContext($context);
        $tester->setDryRun($this->dryRun);
        $tester->setAllowInstability($this->allowInstability);

        return $background->accept($tester);
    }

    /**
     * Visits & tests StepNode.
     *
     * @param   Behat\Gherkin\Node\StepNode             $step
     * @param   Behat\Behat\Context\ContextInterface    $context
     * @param   array                                   $tokens         step replacements for tokens
     * @param   boolean                                 $skip           mark step as skipped?
     * @param   boolean                                 $clone          clone step before running?
     *
     * @uses    Behat\Behat\Tester\StepTester::visit()
     *
     * @return  integer
     */
    protected function visitStep(StepNode $step, ContextInterface $context, array $tokens = array(),
                                 $skip = false, $clone = false)
    {
        $step = $clone ? clone $step : $step;

        $tester = $this->container->get('behat.tester.step');
        $tester->setContext($context);
        $tester->setTokens($tokens);
        $tester->skip($skip || $this->dryRun);
        $tester->unstable($this->unstable);
        $tester->setAllowInstability($this->allowInstability);

        return $step->accept($tester);
    }
}

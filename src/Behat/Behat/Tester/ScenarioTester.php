<?php

namespace Behat\Behat\Tester;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;

use Behat\Gherkin\Node\NodeVisitorInterface,
    Behat\Gherkin\Node\AbstractNode,
    Behat\Gherkin\Node\BackgroundNode,
    Behat\Gherkin\Node\OutlineNode,
    Behat\Gherkin\Node\ScenarioNode,
    Behat\Gherkin\Node\StepNode;

use Behat\Behat\Context\ContextInterface,
    Behat\Behat\Event\ScenarioEvent;

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
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ScenarioTester implements NodeVisitorInterface
{
    protected $container;
    protected $dispatcher;
    private $context;
    private $skip = false;

    /**
     * Initializes tester.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container  = $container;
        $this->dispatcher = $container->get('behat.event_dispatcher');
        $this->context    = $container->get('behat.context.dispatcher')->createContext();
    }

    /**
     * Sets tester to dry-run mode.
     *
     * @param Boolean $skip
     */
    public function setSkip($skip = true)
    {
        $this->skip = (bool) $skip;
    }

    /**
     * Visits & tests ScenarioNode.
     *
     * @param AbstractNode $scenario
     *
     * @return integer
     */
    public function visit(AbstractNode $scenario)
    {
        $this->dispatcher->dispatch('beforeScenario', new ScenarioEvent($scenario, $this->context));

        $result = 0;
        $skip   = false;

        // Visit & test background if has one
        if ($scenario->getFeature()->hasBackground()) {
            $bgResult = $this->visitBackground(
                $scenario->getFeature()->getBackground(), $scenario, $this->context
            );
            if (0 !== $bgResult) {
                $skip = true;
            }
            $result = max($result, $bgResult);
        }

        // Visit & test steps
        foreach ($scenario->getSteps() as $step) {
            $stResult = $this->visitStep($step, $scenario, $this->context, array(), $skip);
            if (0 !== $stResult) {
                $skip = true;
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
     * @param BackgroundNode   $background
     * @param ScenarioNode     $logicalParent
     * @param ContextInterface $context
     *
     * @see BackgroundTester::visit()
     *
     * @return integer
     */
    protected function visitBackground(BackgroundNode $background, ScenarioNode $logicalParent,
                                       ContextInterface $context)
    {
        $tester = $this->container->get('behat.tester.background');
        $tester->setLogicalParent($logicalParent);
        $tester->setContext($context);
        $tester->setSkip($this->skip);

        return $background->accept($tester);
    }

    /**
     * Visits & tests StepNode.
     *
     * @param StepNode         $step          step instance
     * @param ScenarioNode     $logicalParent logical parent of the step
     * @param ContextInterface $context       context instance
     * @param array            $tokens        step replacements for tokens
     * @param boolean          $skip          mark step as skipped?
     *
     * @see StepTester::visit()
     *
     * @return integer
     */
    protected function visitStep(StepNode $step, ScenarioNode $logicalParent,
                                 ContextInterface $context, array $tokens = array(), $skip = false)
    {
        if ($logicalParent instanceof OutlineNode) {
            $step = $step->createExampleRowStep($tokens);
        }

        $tester = $this->container->get('behat.tester.step');
        $tester->setLogicalParent($logicalParent);
        $tester->setContext($context);
        $tester->skip($skip || $this->skip);

        return $step->accept($tester);
    }
}

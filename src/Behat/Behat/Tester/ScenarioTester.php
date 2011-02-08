<?php

namespace Behat\Behat\Tester;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;

use Behat\Gherkin\Node\NodeVisitorInterface,
    Behat\Gherkin\Node\AbstractNode,
    Behat\Gherkin\Node\BackgroundNode,
    Behat\Gherkin\Node\StepNode;

use Behat\Behat\Environment\EnvironmentInterface;

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
     * Environment.
     *
     * @var     Behat\Behat\Environment\EnvironmentInterface
     */
    protected $environment;

    /**
     * Initializes tester.
     *
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container    = $container;
        $this->dispatcher   = $container->get('behat.event_dispatcher');
        $this->environment  = $container->get('behat.environment_builder')->build();
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
        $this->dispatcher->notify(new Event($scenario, 'scenario.before', array(
            'environment'   => $this->environment
        )));

        $result = 0;
        $skip   = false;

        // Visit & test background if has one
        if ($scenario->getFeature()->hasBackground()) {
            $bgResult = $this->visitBackground($scenario->getFeature()->getBackground(), $this->environment);
            if (0 !== $bgResult) {
                $skip = true;
            }
            $result = max($result, $bgResult);
        }

        // Visit & test steps
        foreach ($scenario->getSteps() as $step) {
            $stResult = $this->visitStep($step, $this->environment, array(), $skip);
            if (0 !== $stResult) {
                $skip = true;
            }
            $result = max($result, $stResult);
        }

        $this->dispatcher->notify(new Event($scenario, 'scenario.after', array(
            'result'        => $result,
            'skipped'       => $skip,
            'environment'   => $this->environment
        )));

        return $result;
    }

    /**
     * Visits & tests BackgroundNode.
     *
     * @param   Behat\Gherkin\Node\BackgroundNode               $background
     * @param   Behat\Behat\Environment\EnvironmentInterface    $environment
     *
     * @uses    Behat\Behat\Tester\BackgroundTester::visit()
     *
     * @return  integer
     */
    protected function visitBackground(BackgroundNode $background, EnvironmentInterface $environment)
    {
        $tester = $this->container->get('behat.tester.background');
        $tester->setEnvironment($environment);

        return $background->accept($tester);
    }

    /**
     * Visits & tests StepNode.
     *
     * @param   Behat\Gherkin\Node\StepNode                     $step
     * @param   Behat\Behat\Environment\EnvironmentInterface    $environment
     * @param   array                                           $tokens         step replacements for tokens
     * @param   boolean                                         $skip           mark step as skipped?
     *
     * @uses    Behat\Behat\Tester\StepTester::visit()
     *
     * @return  integer
     */
    protected function visitStep(StepNode $step, EnvironmentInterface $environment, array $tokens = array(), 
                                 $skip = false)
    {
        $tester = $this->container->get('behat.tester.step');
        $tester->setEnvironment($environment);
        $tester->setTokens($tokens);
        $tester->skip($skip);

        return $step->accept($tester);
    }
}

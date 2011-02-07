<?php

namespace Behat\Behat\Tester;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;

use Behat\Gherkin\Node\NodeVisitorInterface,
    Behat\Gherkin\Node\AbstractNode;

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
     * @var     ContainerInterface
     */
    protected $container;
    /**
     * Event dispatcher.
     *
     * @var     EventDispatcher
     */
    protected $dispatcher;
    /**
     * Environment.
     *
     * @var     EnvironmentInterface
     */
    protected $environment;

    /**
     * Initialize tester.
     *
     * @param   ContainerInterface  $container  service container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container    = $container;
        $this->dispatcher   = $container->get('behat.event_dispatcher');
        $this->environment  = $container->get('behat.environment_builder')->build();
    }

    /**
     * Visit ScenarioNode & it's steps.
     *
     * @param   AbstractNode    $scenario       scenario node
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
            $tester = $this->container->get('behat.tester.background');
            $tester->setEnvironment($this->environment);

            $bgResult = $scenario->getFeature()->getBackground()->accept($tester);

            if (0 !== $bgResult) {
                $skip = true;
            }
            $result = max($result, $bgResult);
        }

        // Visit & test steps
        foreach ($scenario->getSteps() as $step) {
            $tester = $this->container->get('behat.tester.step');
            $tester->setEnvironment($this->environment);
            $tester->skip($skip);

            $stResult = $step->accept($tester);

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
}

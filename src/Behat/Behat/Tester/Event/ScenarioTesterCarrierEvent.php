<?php

namespace Behat\Behat\Tester\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Behat\Tester\Event\ContextualTesterCarrierEvent;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\ScenarioNode;

/**
 * Scenario tester carrier event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ScenarioTesterCarrierEvent extends ContextualTesterCarrierEvent
{
    /**
     * @var ScenarioNode
     */
    private $scenario;

    /**
     * Initializes event.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param ScenarioNode         $scenario
     */
    public function __construct(SuiteInterface $suite, ContextPoolInterface $contexts, ScenarioNode $scenario)
    {
        parent::__construct($suite, $contexts);

        $this->scenario = $scenario;
    }

    /**
     * Returns scenario node.
     *
     * @return ScenarioNode
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    /**
     * Checks if scenario is actually an outline.
     *
     * @return Boolean
     */
    public function isOutline()
    {
        return $this->scenario instanceof OutlineNode;
    }
}

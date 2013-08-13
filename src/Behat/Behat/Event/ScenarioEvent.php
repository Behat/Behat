<?php

namespace Behat\Behat\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Gherkin\Node\ScenarioNode;

/**
 * Scenario event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ScenarioEvent extends StepCollectionEvent
{
    /**
     * @var ScenarioNode
     */
    private $scenario;

    /**
     * Initializes scenario event.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param ScenarioNode         $scenario
     * @param integer              $result
     */
    public function __construct(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        ScenarioNode $scenario,
        $result = null
    )
    {
        parent::__construct($suite, $contexts, $result);

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
}

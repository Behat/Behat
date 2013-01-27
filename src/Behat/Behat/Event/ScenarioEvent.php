<?php

namespace Behat\Behat\Event;

use Behat\Behat\Context\ContextInterface;

use Behat\Gherkin\Node\ScenarioNode;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Scenario event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ScenarioEvent extends BaseScenarioEvent
{
    private $scenario;

    /**
     * Initializes scenario event.
     *
     * @param ScenarioNode     $scenario
     * @param ContextInterface $context
     * @param integer          $result
     * @param Boolean          $skipped
     */
    public function __construct(ScenarioNode $scenario, ContextInterface $context, $result = null,
                                $skipped = false)
    {
        $this->scenario = $scenario;

        parent::__construct($context, $result, $skipped);
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

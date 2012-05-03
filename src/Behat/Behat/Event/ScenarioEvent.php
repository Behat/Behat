<?php

namespace Behat\Behat\Event;

use Symfony\Component\EventDispatcher\Event;

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
class ScenarioEvent extends Event implements EventInterface
{
    private $scenario;
    private $context;
    private $result;
    private $skipped;

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
        $this->context  = $context;
        $this->result   = $result;
        $this->skipped  = $skipped;
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
     * Returns context object.
     *
     * @return ContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Returns scenario tester result code.
     *
     * @return integer
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Checks whether scenario were skipped.
     *
     * @return Boolean
     */
    public function isSkipped()
    {
        return $this->skipped;
    }
}

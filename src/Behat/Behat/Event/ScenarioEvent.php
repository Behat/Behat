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
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ScenarioEvent extends Event implements EventInterface
{
    private $scenario;
    private $context;
    private $result;
    private $skipped;
    private $attempts;

    /**
     * Initializes scenario event.
     *
     * @param   Behat\Gherkin\Node\ScenarioNode         $scenario
     * @param   Behat\Behat\Context\ContextInterface    $context
     * @param   integer                                 $result
     * @param   Boolean                                 $skipped
     * @param   integer                                 $attepmts
     */
    public function __construct(ScenarioNode $scenario, ContextInterface $context, $result = null,
                                $skipped = false, $attempts = null)
    {
        $this->scenario = $scenario;
        $this->context  = $context;
        $this->result   = $result;
        $this->skipped  = $skipped;
        $this->attempts = $attempts;
    }

    /**
     * Returns scenario node.
     *
     * @return  Behat\Gherkin\Node\ScenarioNode
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    /**
     * Returns context object.
     *
     * @return  Behat\Behat\Context\ContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Returns scenario tester result code.
     *
     * @return  integer
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Checks whether scenario were skipped.
     *
     * @return  Boolean
     */
    public function isSkipped()
    {
        return $this->skipped;
    }

    /**
     * Get count on scenario attempts.
     *
     * @return integer|null
     */
    public function getAttempts()
    {
        return $this->attempts;
    }

    /**
     * Checks wheter scenario has attempts.
     *
     * @return bool
     */
    public function hasAttempts()
    {
        return null !== $this->getAttempts();
    }

    /**
     * Checks wheter scenario has been completet after
     * more then one attempt and is consideret as unstable.
     *
     * @return Boolean
     */
    public function isUnstable()
    {
        return 1 < intval($this->getAttempts());
    }
}

<?php

namespace Behat\Behat\Event;

use Symfony\Component\EventDispatcher\Event;

use Behat\Behat\Environment\EnvironmentInterface;

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
    private $environment;
    private $result;
    private $skipped;

    /**
     * Initializes scenario event.
     *
     * @param   Behat\Gherkin\Node\ScenarioNode                 $scenario
     * @param   Behat\Behat\Environment\EnvironmentInterface    $environment
     * @param   integer                                         $result
     * @param   Boolean                                         $skipped
     */
    public function __construct(ScenarioNode $scenario, EnvironmentInterface $environment, $result = null,
                                $skipped = false)
    {
        $this->scenario     = $scenario;
        $this->environment  = $environment;
        $this->result       = $result;
        $this->skipped      = $skipped;
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
     * Returns environment object.
     *
     * @return  Behat\Behat\Environment\EnvironmentInterface
     */
    public function getEnvironment()
    {
        return $this->environment;
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
}

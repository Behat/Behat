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
use Behat\Gherkin\Node\BackgroundNode;
use Behat\Gherkin\Node\ScenarioNode;

/**
 * Background event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BackgroundEvent extends StepCollectionEvent
{
    /**
     * @var ScenarioNode
     */
    private $scenario;
    /**
     * @var BackgroundNode
     */
    private $background;

    /**
     * Initializes background event.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param ScenarioNode         $scenario
     * @param BackgroundNode       $background
     * @param integer              $result
     */
    public function __construct(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        ScenarioNode $scenario,
        BackgroundNode $background,
        $result = null
    )
    {
        parent::__construct($suite, $contexts, $result);

        $this->scenario = $scenario;
        $this->background = $background;
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
     * Returns background node.
     *
     * @return BackgroundNode
     */
    public function getBackground()
    {
        return $this->background;
    }
}

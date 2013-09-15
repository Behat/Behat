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
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Gherkin\Node\StepContainerInterface;

/**
 * Background event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BackgroundEvent extends StepCollectionEvent
{
    /**
     * @var ScenarioInterface
     */
    private $scenario;
    /**
     * @var StepContainerInterface
     */
    private $container;
    /**
     * @var BackgroundNode
     */
    private $background;

    /**
     * Initializes background event.
     *
     * @param SuiteInterface         $suite
     * @param ContextPoolInterface   $contexts
     * @param ScenarioInterface      $scenario
     * @param StepContainerInterface $container
     * @param BackgroundNode         $background
     * @param null|integer           $status
     */
    public function __construct(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        ScenarioInterface $scenario,
        StepContainerInterface $container,
        BackgroundNode $background,
        $status = null
    )
    {
        parent::__construct($suite, $contexts, $status);

        $this->scenario = $scenario;
        $this->container = $container;
        $this->background = $background;
    }

    /**
     * Returns scenario node.
     *
     * @return ScenarioInterface
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    /**
     * Returns logical step container this background was runned for.
     *
     * - For scenario, this would be a scenario itself
     * - For outline example, this would be an outline example itself
     *
     * @return StepContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
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

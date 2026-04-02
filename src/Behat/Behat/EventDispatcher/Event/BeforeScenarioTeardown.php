<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Event;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioLikeInterface as Scenario;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\EventDispatcher\Event\BeforeTeardown;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Represents an event before scenario teardown.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @api
 */
final class BeforeScenarioTeardown extends ScenarioTested implements BeforeTeardown
{
    /**
     * Initializes event.
     */
    public function __construct(
        Environment $env,
        private readonly FeatureNode $feature,
        private readonly Scenario $scenario,
        private readonly TestResult $result,
    ) {
        parent::__construct($env);
    }

    /**
     * Returns feature.
     */
    public function getFeature(): FeatureNode
    {
        return $this->feature;
    }

    public function getScenario(): Scenario
    {
        return $this->scenario;
    }

    /**
     * Returns current test result.
     */
    public function getTestResult(): TestResult
    {
        return $this->result;
    }
}

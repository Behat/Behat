<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Event;

use Behat\Gherkin\Node\BackgroundNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioLikeInterface;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\EventDispatcher\Event\AfterTested;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Represents an event in which background was tested.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterBackgroundTested extends BackgroundTested implements AfterTested
{
    /**
     * Initializes event.
     */
    public function __construct(
        Environment $env,
        private readonly FeatureNode $feature,
        private readonly BackgroundNode $background,
        private readonly TestResult $result,
        private readonly Teardown $teardown,
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

    /**
     * Returns scenario node.
     *
     * @deprecated Use getBackground() instead
     *
     * @return ScenarioLikeInterface
     */
    public function getScenario()
    {
        return $this->background;
    }

    /**
     * Returns background node.
     */
    public function getBackground(): BackgroundNode
    {
        return $this->background;
    }

    /**
     * Returns current test result.
     */
    public function getTestResult(): TestResult
    {
        return $this->result;
    }

    /**
     * Returns current test teardown.
     */
    public function getTeardown(): Teardown
    {
        return $this->teardown;
    }
}

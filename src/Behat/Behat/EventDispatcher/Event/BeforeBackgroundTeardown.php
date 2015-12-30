<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Event;

use Behat\Behat\Tester\Context\BackgroundContext;
use Behat\Gherkin\Node\BackgroundNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Testwork\EventDispatcher\Event\BeforeTeardown;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Represents an event right before background teardown.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeBackgroundTeardown extends BackgroundTested implements BeforeTeardown
{
    /**
     * @var FeatureNode
     */
    private $feature;
    /**
     * @var BackgroundNode
     */
    private $background;
    /**
     * @var TestResult
     */
    private $result;

    /**
     * Initializes event.
     *
     * @param BackgroundContext $context
     * @param TestResult        $result
     */
    public function __construct(BackgroundContext $context, TestResult $result)
    {
        parent::__construct($context->getEnvironment());

        $this->feature = $context->getFeature();
        $this->background = $context->getBackground();
        $this->result = $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventName()
    {
        return self::BEFORE_TEARDOWN;
    }

    /**
     * Returns feature.
     *
     * @return FeatureNode
     */
    public function getFeature()
    {
        return $this->feature;
    }

    /**
     * Returns scenario node.
     *
     * @return ScenarioInterface
     */
    public function getScenario()
    {
        return $this->background;
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

    /**
     * Returns current test result.
     *
     * @return TestResult
     */
    public function getTestResult()
    {
        return $this->result;
    }
}

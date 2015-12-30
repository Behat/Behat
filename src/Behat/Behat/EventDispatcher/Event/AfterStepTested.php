<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Event;

use Behat\Behat\Tester\Context\StepContext;
use Behat\Behat\Tester\Result\ExecutedStepResult;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\EventDispatcher\Event\AfterTested;
use Behat\Testwork\Tester\Result\ExceptionResult;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Represents an event after step has been tested.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterStepTested extends StepTested implements AfterTested
{
    /**
     * @var FeatureNode
     */
    private $feature;
    /**
     * @var StepNode
     */
    private $step;
    /**
     * @var StepResult
     */
    private $result;
    /**
     * @var Teardown
     */
    private $teardown;

    /**
     * Initializes event.
     *
     * @param StepContext $context
     * @param StepResult  $result
     * @param Teardown    $teardown
     */
    public function __construct(StepContext $context, StepResult $result, Teardown $teardown)
    {
        parent::__construct($context->getEnvironment());

        $this->feature = $context->getFeature();
        $this->step = $context->getStep();
        $this->result = $result;
        $this->teardown = $teardown;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventName()
    {
        return self::AFTER;
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
     * Returns step node.
     *
     * @return StepNode
     */
    public function getStep()
    {
        return $this->step;
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

    /**
     * Returns current test teardown.
     *
     * @return Teardown
     */
    public function getTeardown()
    {
        return $this->teardown;
    }

    /**
     * Checks if step call, setup or teardown produced any output (stdOut or exception).
     *
     * @return Boolean
     */
    public function hasOutput()
    {
        return $this->teardownHasOutput() || $this->resultHasException() || $this->resultCallHasOutput();
    }

    /**
     * Checks if step teardown has output.
     *
     * @return Boolean
     */
    private function teardownHasOutput()
    {
        return $this->teardown->hasOutput();
    }

    /**
     * Checks if result has produced exception.
     *
     * @return Boolean
     */
    private function resultHasException()
    {
        return $this->result instanceof ExceptionResult && $this->result->getException();
    }

    /**
     * Checks if result is executed and call result has produced exception or stdOut.
     *
     * @return Boolean
     */
    private function resultCallHasOutput()
    {
        if (!$this->result instanceof ExecutedStepResult) {
            return false;
        }

        return $this->result->getCallResult()->hasStdOut() || $this->result->getCallResult()->hasException();
    }
}

<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\EventDispatcher\Event;

use Behat\Behat\Tester\Result\ExecutedStepResult;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;
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
     * @param Environment $env
     * @param FeatureNode $feature
     * @param StepNode    $step
     * @param StepResult  $result
     * @param Teardown    $teardown
     */
    public function __construct(
        Environment $env,
        FeatureNode $feature,
        StepNode $step,
        StepResult $result,
        Teardown $teardown
    ) {
        parent::__construct($env);

        $this->feature = $feature;
        $this->step = $step;
        $this->result = $result;
        $this->teardown = $teardown;
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
     * @return bool
     */
    public function hasOutput()
    {
        return $this->teardownHasOutput() || $this->resultHasException() || $this->resultCallHasOutput();
    }

    /**
     * Checks if step teardown has output.
     *
     * @return bool
     */
    private function teardownHasOutput()
    {
        return $this->teardown->hasOutput();
    }

    /**
     * Checks if result has produced exception.
     *
     * @return bool
     */
    private function resultHasException()
    {
        return $this->result instanceof ExceptionResult && $this->result->getException();
    }

    /**
     * Checks if result is executed and call result has produced exception or stdOut.
     *
     * @return bool
     */
    private function resultCallHasOutput()
    {
        if (!$this->result instanceof ExecutedStepResult) {
            return false;
        }

        return $this->result->getCallResult()->hasStdOut() || $this->result->getCallResult()->hasException();
    }
}

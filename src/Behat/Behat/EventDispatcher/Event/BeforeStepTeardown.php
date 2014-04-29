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
use Behat\Testwork\EventDispatcher\Event\BeforeTeardown;
use Behat\Testwork\Tester\Result\ExceptionResult;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Represents an event before step teardown.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeStepTeardown extends StepTested implements BeforeTeardown
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
     * Initializes event.
     *
     * @param Environment $env
     * @param FeatureNode $feature
     * @param StepNode    $step
     * @param StepResult  $result
     */
    public function __construct(
        Environment $env,
        FeatureNode $feature,
        StepNode $step,
        StepResult $result
    ) {
        parent::__construct($env);

        $this->feature = $feature;
        $this->step = $step;
        $this->result = $result;
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
     * Checks if step call produced any output (stdOut or exception).
     *
     * @return Boolean
     */
    public function hasOutput()
    {
        return $this->resultHasException() || $this->resultCallHasOutput();
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

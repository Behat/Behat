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
     * Initializes event.
     */
    public function __construct(
        Environment $env,
        private readonly FeatureNode $feature,
        private readonly StepNode $step,
        private readonly StepResult $result,
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
     * Returns step node.
     */
    public function getStep(): StepNode
    {
        return $this->step;
    }

    /**
     * Returns current test result.
     */
    public function getTestResult(): TestResult
    {
        return $this->result;
    }

    /**
     * Checks if step call produced any output (stdOut or exception).
     */
    public function hasOutput(): bool
    {
        return $this->resultHasException() || $this->resultCallHasOutput();
    }

    /**
     * Checks if result has produced exception.
     */
    private function resultHasException(): bool
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

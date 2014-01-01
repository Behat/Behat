<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Event;

use Behat\Behat\Tester\Result\StepTestResult;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Call\CallResults;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Hook\Event\LifecycleEvent;
use Behat\Testwork\Suite\Suite;

/**
 * Step event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class StepTested extends LifecycleEvent
{
    const BEFORE = 'tester.step_tested.before';
    const AFTER = 'tester.step_tested.after';

    /**
     * @var StepNode
     */
    private $step;
    /**
     * @var StepTestResult
     */
    private $testResult;
    /**
     * @var null|CallResults
     */
    private $hookCallResults;

    /**
     * Initializes event.
     *
     * @param Suite               $suite
     * @param Environment         $environment
     * @param FeatureNode         $feature
     * @param StepNode            $step
     * @param null|StepTestResult $testResult
     * @param null|CallResults    $hookCallResults
     */
    public function __construct(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        StepNode $step,
        StepTestResult $testResult = null,
        CallResults $hookCallResults = null
    ) {
        parent::__construct($suite, $environment, $feature);

        $this->step = $step;
        $this->testResult = $testResult;
        $this->hookCallResults = $hookCallResults;
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
     * Returns step test result.
     *
     * @return null|StepTestResult
     */
    public function getTestResult()
    {
        return $this->testResult;
    }

    /**
     * Returns hook call results.
     *
     * @return null|CallResults
     */
    public function getHookCallResults()
    {
        return $this->hookCallResults;
    }

    /**
     * Returns step tester result status.
     *
     * @return integer
     */
    public function getResultCode()
    {
        return $this->testResult->getResultCode();
    }
}

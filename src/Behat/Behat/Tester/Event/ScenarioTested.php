<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Event;

use Behat\Behat\Tester\Result\StepContainerTestResult;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Testwork\Call\CallResults;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Suite\Suite;

/**
 * Scenario event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ScenarioTested extends AbstractScenarioTested
{
    const BEFORE = 'tester.scenario_tested.before';
    const AFTER = 'tester.scenario_tested.after';

    /**
     * @var ScenarioNode
     */
    private $scenario;
    /**
     * @var StepContainerTestResult
     */
    private $testResult;
    /**
     * @var null|CallResults
     */
    private $hookCallResults;

    /**
     * Initializes event.
     *
     * @param Suite                   $suite
     * @param Environment             $environment
     * @param FeatureNode             $feature
     * @param ScenarioNode            $scenario
     * @param null|StepContainerTestResult $testResult
     * @param null|CallResults        $hookCallResults
     */
    public function __construct(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        ScenarioNode $scenario,
        StepContainerTestResult $testResult = null,
        CallResults $hookCallResults = null
    ) {
        parent::__construct($suite, $environment, $feature);

        $this->scenario = $scenario;
        $this->testResult = $testResult;
        $this->hookCallResults = $hookCallResults;
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
     * Returns scenario test result (if scenario was tested).
     *
     * @return null|StepContainerTestResult
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

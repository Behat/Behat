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
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Call\CallResults;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Suite\Suite;

/**
 * Outline example tested event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ExampleTested extends AbstractScenarioTested
{
    const BEFORE = 'tester.example_tested.before';
    const AFTER = 'tester.example_tested.after';

    /**
     * @var ExampleNode
     */
    private $example;
    /**
     * @var null|StepContainerTestResult
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
     * @param ExampleNode             $example
     * @param null|StepContainerTestResult $testResult
     * @param null|CallResults        $hookCallResults
     */
    public function __construct(
        Suite $suite,
        Environment $environment,
        FeatureNode $feature,
        ExampleNode $example,
        StepContainerTestResult $testResult = null,
        CallResults $hookCallResults = null
    ) {
        parent::__construct($suite, $environment, $feature);

        $this->example = $example;
        $this->testResult = $testResult;
        $this->hookCallResults = $hookCallResults;
    }

    /**
     * Returns example node.
     *
     * @return ExampleNode
     */
    public function getExample()
    {
        return $this->example;
    }

    /**
     * Returns example test result (if scenario was tested).
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

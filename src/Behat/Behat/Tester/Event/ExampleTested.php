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
     * Initializes event.
     *
     * @param Environment             $environment
     * @param FeatureNode             $feature
     * @param ExampleNode             $scenario
     * @param StepContainerTestResult $testResult
     * @param CallResults             $hookCallResults
     */
    public function __construct(
        Environment $environment,
        FeatureNode $feature,
        ExampleNode $scenario,
        StepContainerTestResult $testResult = null,
        CallResults $hookCallResults = null
    ) {
        parent::__construct($environment, $feature, $scenario, $testResult, $hookCallResults);
    }

    /**
     * @return ExampleNode
     */
    public function getScenario()
    {
        return parent::getScenario();
    }
}

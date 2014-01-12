<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Suite\Suite;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;

/**
 * Background tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BackgroundTester
{
    /**
     * @var StepTester
     */
    private $stepTester;

    /**
     * Initializes tester.
     *
     * @param StepTester $stepTester
     */
    public function __construct(StepTester $stepTester)
    {
        $this->stepTester = $stepTester;
    }

    /**
     * Tests feature backgrounds.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param Boolean     $skip
     *
     * @return TestResults
     */
    public function test(Suite $suite, Environment $environment, FeatureNode $feature, $skip = false)
    {
        $results = $this->testBackground($suite, $environment, $feature, $skip);

        return $results;
    }

    /**
     * Tests background.
     *
     * @param Suite       $suite
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param Boolean     $skip
     *
     * @return TestResults
     */
    protected function testBackground(Suite $suite, Environment $environment, FeatureNode $feature, $skip = false)
    {
        $background = $feature->getBackground();

        $results = array();
        foreach ($background->getSteps() as $step) {
            $results[] = $lastResult = $this->stepTester->test($suite, $environment, $feature, $step, $skip);
            $skip = TestResult::PASSED < $lastResult->getResultCode() ? true : $skip;
        }

        return new TestResults($results);
    }
}

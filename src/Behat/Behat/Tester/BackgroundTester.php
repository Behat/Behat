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
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param Boolean     $skip
     *
     * @return TestResults
     */
    public function test(Environment $environment, FeatureNode $feature, $skip = false)
    {
        $results = $this->testBackground($environment, $feature, $skip);

        return $results;
    }

    /**
     * Tests background.
     *
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param Boolean     $skip
     *
     * @return TestResults
     */
    protected function testBackground(Environment $environment, FeatureNode $feature, $skip = false)
    {
        $background = $feature->getBackground();

        $results = array();
        foreach ($background->getSteps() as $step) {
            $results[] = $lastResult = $this->stepTester->test($environment, $feature, $step, $skip);
            $skip = TestResult::PASSED < $lastResult->getResultCode() ? true : $skip;
        }

        return new TestResults($results);
    }
}

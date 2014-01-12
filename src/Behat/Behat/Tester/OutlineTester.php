<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester;

use Behat\Behat\Tester\Result\OutlineTestResult;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;

/**
 * Outline tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutlineTester
{
    /**
     * @var StepContainerTester
     */
    private $exampleTester;

    /**
     * Initializes tester.
     *
     * @param StepContainerTester $exampleTester
     */
    public function __construct(StepContainerTester $exampleTester)
    {
        $this->exampleTester = $exampleTester;
    }

    /**
     * Tests outline.
     *
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param OutlineNode $outline
     * @param Boolean     $skip
     *
     * @return TestResult
     */
    public function test(Environment $environment, FeatureNode $feature, OutlineNode $outline, $skip = false)
    {
        $result = $this->testOutline($environment, $feature, $outline, $skip);

        return new TestResult($result->getResultCode());
    }

    /**
     * Tests outline examples.
     *
     * @param Environment $environment
     * @param FeatureNode $feature
     * @param OutlineNode $outline
     * @param Boolean     $skip
     *
     * @return OutlineTestResult
     */
    protected function testOutline(
        Environment $environment,
        FeatureNode $feature,
        OutlineNode $outline,
        $skip = false
    ) {
        $results = array();
        foreach ($outline->getExamples() as $example) {
            $results[] = $this->exampleTester->test($environment, $feature, $example, $skip);
        }

        return new OutlineTestResult(new TestResults($results));
    }
}

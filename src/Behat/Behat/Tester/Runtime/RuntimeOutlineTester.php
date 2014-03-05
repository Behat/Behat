<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Runtime;

use Behat\Behat\Tester\ExampleTester;
use Behat\Behat\Tester\OutlineTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Exception\TestworkException;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Exception;

/**
 * Behat in-runtime outline tester.
 *
 * Outline tester executing outline tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RuntimeOutlineTester implements OutlineTester
{
    /**
     * @var ExampleTester
     */
    private $exampleTester;

    /**
     * Initializes tester.
     *
     * @param ExampleTester $exampleTester
     */
    public function __construct(ExampleTester $exampleTester)
    {
        $this->exampleTester = $exampleTester;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $environment, FeatureNode $feature, OutlineNode $outline, $skip)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $environment, FeatureNode $feature, OutlineNode $outline, $skip = false)
    {
        $results = array();
        foreach ($outline->getExamples() as $example) {
            try {
                $this->exampleTester->setUp($environment, $feature, $example, $skip);
            } catch (TestworkException $e) {
                throw $e;
            } catch (Exception $e) {
                $skip = true;
            }

            $testResult = $this->exampleTester->test($environment, $feature, $example, $skip);

            try {
                $this->exampleTester->tearDown($environment, $feature, $example, $skip, $testResult);
            } catch (TestworkException $e) {
                throw $e;
            } catch (Exception $e) {
                $skip = true;
            }

            $results[] = new TestResult($testResult->getResultCode());
        }

        return new TestResults($results);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(
        Environment $environment,
        FeatureNode $feature,
        OutlineNode $outline,
        $skip,
        TestResult $result
    ) {
    }
}

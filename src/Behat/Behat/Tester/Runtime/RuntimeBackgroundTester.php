<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Runtime;

use Behat\Behat\Tester\BackgroundTester;
use Behat\Behat\Tester\Exception\TearDownException;
use Behat\Behat\Tester\Result\BehatTestResult;
use Behat\Behat\Tester\StepTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Exception\TestworkException;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Exception;

/**
 * Behat in-runtime background tester.
 *
 * Background tester executing background tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RuntimeBackgroundTester implements BackgroundTester
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
     * {@inheritdoc}
     */
    public function setUp(Environment $environment, FeatureNode $feature, $skip)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $environment, FeatureNode $feature, $skip)
    {
        $background = $feature->getBackground();

        $results = array();
        foreach ($background->getSteps() as $step) {
            try {
                $this->stepTester->setUp($environment, $feature, $step, $skip);
            } catch (TestworkException $e) {
                throw $e;
            } catch (Exception $e) {
                $skip = true;
            }

            $testResult = $this->stepTester->test($environment, $feature, $step, $skip);

            try {
                $this->stepTester->tearDown($environment, $feature, $step, $skip, $testResult);
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
    public function tearDown(Environment $environment, FeatureNode $feature, $skip, TestResults $results)
    {
        if (BehatTestResult::PASSED < $results->getResultCode()) {
            throw new TearDownException('Some step tests have failed.');
        }
    }
}

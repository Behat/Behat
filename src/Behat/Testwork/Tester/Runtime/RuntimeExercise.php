<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Runtime;

use Behat\Testwork\Environment\EnvironmentManager;
use Behat\Testwork\Exception\TestworkException;
use Behat\Testwork\Specification\GroupedSpecificationIterator;
use Behat\Testwork\Tester\Exercise;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Behat\Testwork\Tester\SuiteTester;
use Exception;

/**
 * Testwork in-runtime exercise.
 *
 * Tester executing exercises in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RuntimeExercise implements Exercise
{
    /**
     * @var EnvironmentManager
     */
    private $environmentManager;
    /**
     * @var SuiteTester
     */
    private $suiteTester;

    /**
     * Initializes tester.
     *
     * @param EnvironmentManager $environmentManager
     * @param SuiteTester        $suiteTester
     */
    public function __construct(EnvironmentManager $environmentManager, SuiteTester $suiteTester)
    {
        $this->environmentManager = $environmentManager;
        $this->suiteTester = $suiteTester;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(array $iterators, $skip)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function test(array $iterators, $skip = false)
    {
        $results = array();
        foreach (GroupedSpecificationIterator::group($iterators) as $iterator) {
            $environment = $this->environmentManager->buildEnvironment($iterator->getSuite());

            try {
                $this->suiteTester->setUp($environment, $iterator, $skip);
            } catch (TestworkException $e) {
                throw $e;
            } catch (Exception $e) {
                $skip = true;
            }

            $testResult = $this->suiteTester->test($environment, $iterator, $skip);

            try {
                $this->suiteTester->tearDown($environment, $iterator, $skip, $testResult);
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
    public function tearDown(array $iterators, $skip, TestResult $result)
    {
    }
}

<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Runtime;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Exception\TestworkException;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Behat\Testwork\Tester\SpecificationTester;
use Behat\Testwork\Tester\SuiteTester;
use Exception;

/**
 * Testwork in-runtime suite tester.
 *
 * Tester executing suite tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RuntimeSuiteTester implements SuiteTester
{
    /**
     * @var SpecificationTester
     */
    private $specificationTester;

    /**
     * Initializes tester.
     *
     * @param SpecificationTester $specificationTester
     */
    public function __construct(SpecificationTester $specificationTester)
    {
        $this->specificationTester = $specificationTester;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $environment, SpecificationIterator $iterator, $skip)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $environment, SpecificationIterator $iterator, $skip = false)
    {
        $results = array();
        foreach ($iterator as $specification) {
            try {
                $this->specificationTester->setUp($environment, $specification, $skip);
            } catch (TestworkException $e) {
                throw $e;
            } catch (Exception $e) {
                $skip = true;
            }

            $testResult = $this->specificationTester->test($environment, $specification, $skip);

            try {
                $this->specificationTester->tearDown($environment, $specification, $skip, $testResult);
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
    public function tearDown(Environment $environment, SpecificationIterator $iterator, $skip, TestResult $result)
    {
    }
}

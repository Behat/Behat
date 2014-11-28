<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Exercise;

use Behat\Testwork\Tester\Context\Context;
use Behat\Testwork\Tester\Context\SpecificationContext;
use Behat\Testwork\Tester\Context\SuiteContext;
use Behat\Testwork\Tester\Exception\WrongContextException;
use Behat\Testwork\Tester\Result\IntegerTestResult;
use Behat\Testwork\Tester\Result\TestResults;
use Behat\Testwork\Tester\RunControl;
use Behat\Testwork\Tester\Tester;

/**
 * Tests specification suites.
 *
 * Suites are dynamic collections of specifications.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SuiteTester implements Tester
{
    /**
     * @var Tester
     */
    private $specificationTester;

    /**
     * Initializes tester.
     *
     * @param Tester $specificationTester
     */
    public function __construct(Tester $specificationTester)
    {
        $this->specificationTester = $specificationTester;
    }

    /**
     * {@inheritdoc}
     */
    public function test(Context $context, RunControl $control)
    {
        $context = $this->castContext($context);
        $results = array();

        foreach ($context->getSpecificationIterator() as $specification) {
            $specContext = new SpecificationContext($specification, $context->getEnvironment());
            $testResult = $this->specificationTester->test($specContext, $control);
            $results[] = new IntegerTestResult($testResult->getResultCode());
        }

        return new TestResults($results);
    }

    /**
     * Casts provided context to the expected one.
     *
     * @param Context $context
     *
     * @return SuiteContext
     *
     * @throws WrongContextException
     */
    private function castContext(Context $context)
    {
        if ($context instanceof SuiteContext) {
            return $context;
        }

        throw new WrongContextException(
            sprintf(
                'SuiteTester tests instances of SuiteContext only, but %s given.',
                get_class($context)
            ), $context
        );
    }
}

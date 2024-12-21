<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Event;

use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Testwork\Tester\Setup\Teardown;

/**
 * Represents an event in which suite was tested.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterSuiteTested extends SuiteTested implements AfterTested
{
    /**
     * @var SpecificationIterator<mixed>
     */
    private $iterator;
    /**
     * @var TestResult
     */
    private $result;
    /**
     * @var Teardown
     */
    private $teardown;

    /**
     * Initializes event.
     *
     * @param Environment           $env
     * @param SpecificationIterator<mixed> $iterator
     * @param TestResult            $result
     * @param Teardown              $teardown
     */
    public function __construct(
        Environment $env,
        SpecificationIterator $iterator,
        TestResult $result,
        Teardown $teardown
    ) {
        parent::__construct($env);

        $this->iterator = $iterator;
        $this->result = $result;
        $this->teardown = $teardown;
    }

    public function getSpecificationIterator()
    {
        return $this->iterator;
    }

    /**
     * Returns current test result.
     *
     * @return TestResult
     */
    public function getTestResult()
    {
        return $this->result;
    }

    /**
     * Returns current test teardown.
     *
     * @return Teardown
     */
    public function getTeardown()
    {
        return $this->teardown;
    }
}

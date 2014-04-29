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

/**
 * Represents an event right before suite teardown.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeSuiteTeardown extends SuiteTested implements BeforeTeardown
{
    /**
     * @var SpecificationIterator
     */
    private $iterator;
    /**
     * @var TestResult
     */
    private $result;

    /**
     * Initializes event.
     *
     * @param Environment           $env
     * @param SpecificationIterator $iterator
     * @param TestResult            $result
     */
    public function __construct(Environment $env, SpecificationIterator $iterator, TestResult $result)
    {
        parent::__construct($env);

        $this->iterator = $iterator;
        $this->result = $result;
    }

    /**
     * Returns specification iterator.
     *
     * @return SpecificationIterator
     */
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
}

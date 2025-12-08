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
     * Initializes event.
     *
     * @param SpecificationIterator<mixed> $iterator
     */
    public function __construct(
        Environment $env,
        private readonly SpecificationIterator $iterator,
        private readonly TestResult $result,
        private readonly Teardown $teardown,
    ) {
        parent::__construct($env);
    }

    public function getSpecificationIterator(): SpecificationIterator
    {
        return $this->iterator;
    }

    /**
     * Returns current test result.
     */
    public function getTestResult(): TestResult
    {
        return $this->result;
    }

    /**
     * Returns current test teardown.
     */
    public function getTeardown(): Teardown
    {
        return $this->teardown;
    }
}

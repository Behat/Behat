<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Result;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Aggregates multiple test results into a collection and provides informational API on top of that.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @implements IteratorAggregate<int, TestResult>
 */
final class TestResults implements TestResult, Countable, IteratorAggregate
{
    public const NO_TESTS = -100;

    /**
     * Initializes test results collection.
     *
     * @param TestResult[] $results
     */
    public function __construct(
        private readonly array $results = [],
    ) {
    }

    public function isPassed(): bool
    {
        return self::PASSED == $this->getResultCode();
    }

    /**
     * @return TestResult::*|TestResults::NO_TESTS
     */
    public function getResultCode()
    {
        $resultCode = self::NO_TESTS;
        foreach ($this->results as $result) {
            $resultCode = max($resultCode, $result->getResultCode());
        }

        return $resultCode;
    }

    public function count(): int
    {
        return count($this->results);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->results);
    }

    /**
     * Returns test results array.
     *
     * @return TestResult[]
     */
    public function toArray(): array
    {
        return $this->results;
    }
}

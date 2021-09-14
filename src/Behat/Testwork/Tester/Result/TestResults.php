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
 */
final class TestResults implements TestResult, Countable, IteratorAggregate
{
    public const NO_TESTS = -100;

    /**
     * @var TestResult[]
     */
    private $results;

    /**
     * Initializes test results collection.
     *
     * @param TestResult[] $results
     */
    public function __construct(array $results = array())
    {
        $this->results = $results;
    }

    /**
     * {@inheritdoc}
     */
    public function isPassed()
    {
        return self::PASSED == $this->getResultCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getResultCode()
    {
        $resultCode = static::NO_TESTS;
        foreach ($this->results as $result) {
            $resultCode = max($resultCode, $result->getResultCode());
        }

        return $resultCode;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->results);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->results);
    }

    /**
     * Returns test results array.
     *
     * @return TestResult[]
     */
    public function toArray()
    {
        return $this->results;
    }
}

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
use Iterator;
use IteratorAggregate;

/**
 * Testwork test results collection.
 *
 * Aggregates multiple test results into a collection and provides informational API on top of that.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TestResults extends TestResult implements Countable, IteratorAggregate
{
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
     * Merges results from provided collection into the current one.
     *
     * @param TestResults $first
     * @param TestResults $second
     *
     * @return TestResults
     */
    public static function merge(TestResults $first, TestResults $second)
    {
        return new static($first->toArray(), $second->toArray());
    }

    /**
     * Returns test results.
     *
     * @return integer
     */
    public function getResultCode()
    {
        $resultCode = static::PASSED;
        foreach ($this->results as $result) {
            $resultCode = max($resultCode, $result->getResultCode());
        }

        return $resultCode;
    }

    /**
     * Returns amount of results.
     *
     * @return integer
     */
    public function count()
    {
        return count($this->results);
    }

    /**
     * Returns collection iterator.
     *
     * @return Iterator
     */
    public function getIterator()
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

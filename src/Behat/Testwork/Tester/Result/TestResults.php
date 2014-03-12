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
 * Aggregates multiple test results into a collection and provides informational API on top of that.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class TestResults implements TestResult, Countable, IteratorAggregate
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
     * {@inheritdoc}
     */
    public function isPassed()
    {
        return self::PASSED >= $this->getResultCode();
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->results);
    }

    /**
     * {@inheritdoc}
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

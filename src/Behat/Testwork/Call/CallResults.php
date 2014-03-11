<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Call;

use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;

/**
 * Aggregates multiple call results into a collection and provides an informational API on top of that.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class CallResults implements Countable, IteratorAggregate
{
    /**
     * @var CallResult[]
     */
    private $results;

    /**
     * Initializes call results collection.
     *
     * @param CallResult[] $results
     */
    public function __construct(array $results = array())
    {
        $this->results = $results;
    }

    /**
     * Merges results from provided collection into the current one.
     *
     * @param CallResults $first
     * @param CallResults $second
     *
     * @return CallResults
     */
    public static function merge(CallResults $first, CallResults $second)
    {
        return new static(array_merge($first->toArray(), $second->toArray()));
    }

    /**
     * Checks if any call in collection throws an exception.
     *
     * @return Boolean
     */
    public function hasExceptions()
    {
        foreach ($this->results as $result) {
            if ($result->hasException()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if any call in collection produces an output.
     *
     * @return Boolean
     */
    public function hasStdOuts()
    {
        foreach ($this->results as $result) {
            if ($result->hasStdOut()) {
                return true;
            }
        }

        return false;
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
     * Returns call results array.
     *
     * @return CallResult[]
     */
    public function toArray()
    {
        return $this->results;
    }
}

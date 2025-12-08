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
use IteratorAggregate;

/**
 * Aggregates multiple call results into a collection and provides an informational API on top of that.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @implements IteratorAggregate<int, CallResult>
 */
final class CallResults implements Countable, IteratorAggregate
{
    /**
     * Initializes call results collection.
     *
     * @param CallResult[] $results
     */
    public function __construct(
        private readonly array $results = [],
    ) {
    }

    /**
     * Merges results from provided collection into the current one.
     */
    public static function merge(CallResults $first, CallResults $second): self
    {
        return new self(array_merge($first->toArray(), $second->toArray()));
    }

    /**
     * Checks if any call in collection throws an exception.
     */
    public function hasExceptions(): bool
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
     */
    public function hasStdOuts(): bool
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
     */
    public function count(): int
    {
        return count($this->results);
    }

    /**
     * Returns collection iterator.
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->results);
    }

    /**
     * Returns call results array.
     *
     * @return CallResult[]
     */
    public function toArray(): array
    {
        return $this->results;
    }
}

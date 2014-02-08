<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Specification;

use Behat\Testwork\Suite\Suite;

/**
 * Testwork grouped specification iterator.
 *
 * Iterates over specification iterators grouped by their suite.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class GroupedSpecificationIterator implements SpecificationIterator
{
    /**
     * @var Suite
     */
    private $suite;
    /**
     * @var SpecificationIterator[]
     */
    private $iterators;
    /**
     * @var integer
     */
    private $position = 0;

    /**
     * Initializes iterator.
     *
     * @param Suite                   $suite
     * @param SpecificationIterator[] $specificationIterators
     */
    public function __construct(Suite $suite, array $specificationIterators)
    {
        $this->suite = $suite;
        $this->iterators = $specificationIterators;
    }

    /**
     * Groups specifications by their suite.
     *
     * @param SpecificationIterator[] $specificationIterators
     *
     * @return GroupedSpecificationIterator[]
     */
    public static function group(array $specificationIterators)
    {
        $groupedSpecifications = array();
        foreach ($specificationIterators as $specificationIterator) {
            $groupedSpecifications[$specificationIterator->getSuite()->getName()][] = $specificationIterator;
        }

        return array_map(
            function ($iterator) {
                return new GroupedSpecificationIterator($iterator[0]->getSuite(), $iterator);
            },
            $groupedSpecifications
        );
    }

    /**
     * Returns suite that was used to load specifications.
     *
     * @return Suite
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * Rewinds the Iterator to the first element.
     */
    public function rewind()
    {
        $this->position = 0;
        while (isset($this->iterators[$this->position])) {
            $this->iterators[$this->position]->rewind();

            if ($this->iterators[$this->position]->valid()) {
                break;
            }
            $this->position++;
        }
    }

    /**
     * Moves forward to the next element.
     */
    public function next()
    {
        $this->iterators[$this->position]->next();
        while (!$this->iterators[$this->position]->valid()) {
            $this->position++;

            if (!isset($this->iterators[$this->position])) {
                break;
            }

            $this->iterators[$this->position]->rewind();
        }
    }

    /**
     * Checks if current position is valid.
     *
     * @return Boolean
     */
    public function valid()
    {
        return isset($this->iterators[$this->position]) && $this->iterators[$this->position]->valid();
    }

    /**
     * Returns the current element.
     *
     * @return null|mixed
     */
    public function current()
    {
        return $this->iterators[$this->position]->current();
    }

    /**
     * Returns the key of the current element.
     *
     * @return string
     */
    public function key()
    {
        return $this->position + $this->iterators[$this->position]->key();
    }
}

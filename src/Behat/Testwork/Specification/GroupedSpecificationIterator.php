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
 * Iterates over specification iterators grouped by their suite.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @template T
 * @implements SpecificationIterator<T>
 */
final class GroupedSpecificationIterator implements SpecificationIterator
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
     * @param Suite                          $suite
     * @param list<SpecificationIterator<T>> $specificationIterators
     */
    public function __construct(Suite $suite, array $specificationIterators)
    {
        $this->suite = $suite;
        $this->iterators = $specificationIterators;
    }

    /**
     * Groups specifications by their suite.
     *
     * @template TSpec
     *
     * @param SpecificationIterator<TSpec>[] $specificationIterators
     *
     * @return array<string, GroupedSpecificationIterator<TSpec>>
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
     * {@inheritdoc}
     */
    public function getSuite()
    {
        return $this->suite;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
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
     * {@inheritdoc}
     */
    public function next(): void
    {
        if (!isset($this->iterators[$this->position])) {
            return;
        }

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
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        return isset($this->iterators[$this->position]) && $this->iterators[$this->position]->valid();
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->iterators[$this->position]->current();
    }

    /**
     * {@inheritdoc}
     */
    public function key(): int
    {
        return $this->position + $this->iterators[$this->position]->key();
    }
}

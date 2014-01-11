<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Subject;

use Behat\Testwork\Suite\Suite;

/**
 * Testwork grouped subject iterator.
 *
 * Iterates over subject iterators grouped by their suite.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class GroupedSubjectIterator implements SubjectIterator
{
    /**
     * @var Suite
     */
    private $suite;
    /**
     * @var SubjectIterator[]
     */
    private $iterators;
    /**
     * @var integer
     */
    private $position = 0;

    /**
     * Initializes subjects.
     *
     * @param Suite             $suite
     * @param SubjectIterator[] $subjectIterators
     */
    public function __construct(Suite $suite, array $subjectIterators)
    {
        $this->suite = $suite;
        $this->iterators = $subjectIterators;
    }

    /**
     * Groups subjects by their suite.
     *
     * @param SubjectIterator[] $subjectIterators
     *
     * @return GroupedSubjectIterator[]
     */
    public static function group(array $subjectIterators)
    {
        $groupedSubjects = array();
        foreach ($subjectIterators as $subjectIterator) {
            $groupedSubjects[$subjectIterator->getSuite()->getName()][] = $subjectIterator;
        }

        return array_map(
            function ($iterator) {
                return new GroupedSubjectIterator($iterator[0]->getSuite(), $iterator);
            }, $groupedSubjects
        );
    }

    /**
     * Returns suite that was used to load subjects.
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

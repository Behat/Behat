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
 * Testwork grouped subjects iterator.
 *
 * Iterates over subjects iterators grouped by suites.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class GroupedSubjects implements Subjects
{
    /**
     * @var Subjects[]
     */
    private $subjects;
    /**
     * @var integer
     */
    private $position = 0;

    /**
     * Initializes subjects.
     *
     * @param Subjects[] $subjects
     */
    public function __construct(array $subjects)
    {
        $this->subjects = $subjects;
    }

    /**
     * @param Subjects[] $subjects
     *
     * @return GroupedSubjects[]
     */
    public static function group(array $subjects)
    {
        $groupedSubjects = array();
        foreach ($subjects as $collection) {
            if (!isset($groupedSubjects[$collection->getSuite()->getName()])) {
                $groupedSubjects[$collection->getSuite()->getName()] = array();
            }

            $groupedSubjects[$collection->getSuite()->getName()][] = $collection;
        }

        return array_map(
            function ($subjects) {
                return new static($subjects);
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
        return count($this->subjects) ? $this->subjects[0]->getSuite() : null;
    }

    /**
     * Rewinds the Iterator to the first element.
     */
    public function rewind()
    {
        $this->position = 0;
        if (isset($this->subjects[$this->position])) {
            $this->subjects[$this->position]->rewind();
        }
    }

    /**
     * Moves forward to the next element.
     */
    public function next()
    {
        $this->subjects[$this->position]->next();
        if (!$this->subjects[$this->position]->valid()) {
            $this->position++;

            if (isset($this->subjects[$this->position])) {
                $this->subjects[$this->position]->rewind();
            }
        }
    }

    /**
     * Checks if current position is valid.
     *
     * @return Boolean
     */
    public function valid()
    {
        return isset($this->subjects[$this->position]) && $this->subjects[$this->position]->valid();
    }

    /**
     * Returns the current element.
     *
     * @return null|mixed
     */
    public function current()
    {
        return $this->subjects[$this->position]->current();
    }

    /**
     * Returns the key of the current element.
     *
     * @return string
     */
    public function key()
    {
        return $this->position + $this->subjects[$this->position]->key();
    }
}

<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Subject;

use Behat\Testwork\Subject\Locator\SubjectLocator;
use Behat\Testwork\Suite\Suite;

/**
 * Testwork test subject finder.
 *
 * Finds test subjects for provided suites using registered locators.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SubjectFinder
{
    /**
     * @var SubjectLocator[]
     */
    private $subjectLocators = array();

    /**
     * Registers subject locator.
     *
     * @param SubjectLocator $locator
     */
    public function registerSubjectLocator(SubjectLocator $locator)
    {
        $this->subjectLocators[] = $locator;
    }

    /**
     * Finds all subjects for all provided suites matching provided locator and wraps them into a subject iterator.
     *
     * @param Suite[]     $suites
     * @param null|string $locator
     *
     * @return SubjectIterator[]
     */
    public function findSuitesSubjects(array $suites, $locator = null)
    {
        $iterators = array();
        foreach ($suites as $suite) {
            $iterators = array_merge($iterators, $this->findSuiteSubjects($suite, $locator));
        }

        return $iterators;
    }

    /**
     * Creates suite subject iterator for provided locator.
     *
     * @param Suite       $suite
     * @param null|string $locator
     *
     * @return SubjectIterator[]
     */
    private function findSuiteSubjects(Suite $suite, $locator = null)
    {
        $iterators = array();
        foreach ($this->subjectLocators as $subjectLocator) {
            $iterators[] = $subjectLocator->locateSubjects($suite, $locator);
        }

        return $iterators;
    }
}

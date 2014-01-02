<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Subject;

use Behat\Testwork\Subject\Exception\IteratorCreationException;
use Behat\Testwork\Subject\Iterator\IteratorFactory;
use Behat\Testwork\Subject\Iterator\SubjectIterator;
use Behat\Testwork\Suite\Suite;

/**
 * Testwork test subject locator.
 *
 * Locates test subjects for provided suites using registered loaders.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SubjectLocator
{
    /**
     * @var IteratorFactory[]
     */
    private $loaders = array();

    /**
     * Registers subject iterator factory.
     *
     * @param IteratorFactory $loader
     */
    public function registerIteratorFactory(IteratorFactory $loader)
    {
        $this->loaders[] = $loader;
    }

    /**
     * Creates suites subject iterators for provided locator.
     *
     * @param Suite[]     $suites
     * @param null|string $locator
     *
     * @return SubjectIterator[]
     */
    public function createSubjectIterators(array $suites, $locator = null)
    {
        $subjects = array();
        foreach ($suites as $suite) {
            $subjects[] = $this->createSuiteSubjectIterators($suite, $locator);
        }

        return $subjects;
    }

    /**
     * Creates suite subject iterator for provided locator.
     *
     * @param Suite       $suite
     * @param null|string $locator
     *
     * @return SubjectIterator
     *
     * @throws IteratorCreationException If loader for locator not found
     */
    private function createSuiteSubjectIterators(Suite $suite, $locator = null)
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supportsSuiteAndLocator($suite, $locator)) {
                return $loader->createSubjectIterator($suite, $locator);
            }
        }

        throw new IteratorCreationException(sprintf(
            'Can not find subject iterator factory for the `%s` suite and `%s` locator.',
            $suite->getName(),
            $locator
        ), $suite, $locator);
    }
}

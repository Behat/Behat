<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Subject;

use Behat\Testwork\Subject\Exception\SubjectLoadingException;
use Behat\Testwork\Subject\Loader\SubjectsLoader;
use Behat\Testwork\Suite\Suite;

/**
 * Testwork test subject locator.
 *
 * Locates test subjects for provided suites using registered loaders.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SubjectsLocator
{
    /**
     * @var SubjectsLoader[]
     */
    private $loaders = array();

    /**
     * Registers subject loader.
     *
     * @param SubjectsLoader $loader
     */
    public function registerSubjectLoader(SubjectsLoader $loader)
    {
        $this->loaders[] = $loader;
    }

    /**
     * Locates suites subjects for provided suites at locator.
     *
     * @param Suite[]     $suites
     * @param null|string $locator
     *
     * @return Subjects[]
     */
    public function locateTestSubjects(array $suites, $locator = null)
    {
        $subjects = array();
        foreach ($suites as $suite) {
            $subjects[] = $this->locateSuiteSubjects($suite, $locator);
        }

        return $subjects;
    }

    /**
     * Loads suite subjects for provided suite at locator.
     *
     * @param Suite       $suite
     * @param null|string $locator
     *
     * @return Subjects
     *
     * @throws SubjectLoadingException If loader for locator not found
     */
    public function locateSuiteSubjects(Suite $suite, $locator = null)
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supportsSuiteAndLocator($suite, $locator)) {
                return $loader->loadTestSubjects($suite, $locator);
            }
        }

        throw new SubjectLoadingException(sprintf(
            'Can not find subjects loader for the `%s` suite & `%s` locator.',
            $suite->getName(),
            $locator
        ), $suite, $locator);
    }
}

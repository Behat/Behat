<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Subject\Loader;

use Behat\Testwork\Subject\Subjects;
use Behat\Testwork\Subject\SubjectsLocator;
use Behat\Testwork\Suite\Suite;

/**
 * Testwork test subject loader interface.
 *
 * Used by SubjectsLocator.
 *
 * @see SubjectsLocator
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SubjectsLoader
{
    /**
     * Checks if loader supports provided suite & locator.
     *
     * @param Suite  $suite
     * @param string $locator
     *
     * @return Boolean
     */
    public function supportsSuiteAndLocator(Suite $suite, $locator);

    /**
     * Loads test subjects using provided suite & locator.
     *
     * @param Suite  $suite
     * @param string $locator
     *
     * @return Subjects
     */
    public function loadTestSubjects(Suite $suite, $locator);
}

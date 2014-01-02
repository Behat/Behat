<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Subject\Iterator;

use Behat\Testwork\Subject\SubjectIterator;
use Behat\Testwork\Subject\SubjectLocator;
use Behat\Testwork\Suite\Suite;

/**
 * Testwork test subject iterator factory interface.
 *
 * Used by SubjectLocator.
 *
 * @see SubjectLocator
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface IteratorFactory
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
     * Loads test subject iterator using provided suite & locator.
     *
     * @param Suite  $suite
     * @param string $locator
     *
     * @return SubjectIterator
     */
    public function createSubjectIterator(Suite $suite, $locator);
}

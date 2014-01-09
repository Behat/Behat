<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Subject\Locator;

use Behat\Testwork\Subject\SubjectFinder;
use Behat\Testwork\Subject\SubjectIterator;
use Behat\Testwork\Suite\Suite;

/**
 * Testwork test subject locator interface.
 *
 * Used by SubjectFinder.
 *
 * @see SubjectFinder
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SubjectLocator
{
    /**
     * Locates subjects and wraps them into iterator if found and returns null if not found.
     *
     * @param Suite  $suite
     * @param string $locator
     *
     * @return SubjectIterator
     */
    public function locateSubjects(Suite $suite, $locator);
}

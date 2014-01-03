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
use Iterator;

/**
 * Testwork test subject iterator.
 *
 * Iterates over test subjects.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SubjectIterator extends Iterator
{
    /**
     * Returns suite that was used to load subjects.
     *
     * @return Suite
     */
    public function getSuite();
}

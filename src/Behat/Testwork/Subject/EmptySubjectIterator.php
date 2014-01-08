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
use EmptyIterator;

/**
 * Testwork empty subject iterator.
 *
 * Return instance of this class from locator if no subjects are found.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class EmptySubjectIterator extends EmptyIterator implements SubjectIterator
{
    /**
     * @var Suite
     */
    private $suite;

    /**
     * Initializes iterator.
     *
     * @param Suite $suite
     */
    public function __construct(Suite $suite)
    {
        $this->suite = $suite;
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
}

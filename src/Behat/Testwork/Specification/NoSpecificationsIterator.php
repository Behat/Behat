<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Specification;

use Behat\Testwork\Suite\Suite;
use EmptyIterator;

/**
 * Represents empty specification iterator.
 *
 * Return an instance of this class from locator if no specifications are found.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @implements SpecificationIterator<never>
 */
final class NoSpecificationsIterator extends EmptyIterator implements SpecificationIterator
{
    /**
     * Initializes iterator.
     */
    public function __construct(
        private readonly Suite $suite,
    ) {
    }

    /**
     * Returns suite that was used to load subjects.
     */
    public function getSuite(): Suite
    {
        return $this->suite;
    }
}

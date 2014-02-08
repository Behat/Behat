<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Specification\Locator;

use Behat\Testwork\Specification\SpecificationFinder;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Suite\Suite;

/**
 * Testwork test specification locator interface.
 *
 * Used by SpecificationFinder.
 *
 * @see    SpecificationFinder
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SpecificationLocator
{
    /**
     * Locates specifications and wraps them into iterator.
     *
     * @param Suite  $suite
     * @param string $locator
     *
     * @return SpecificationIterator
     */
    public function locateSpecifications(Suite $suite, $locator);
}

<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Specification;

use Behat\Testwork\Suite\Suite;

/**
 * Iterates over test specifications.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SpecificationIterator extends \Iterator
{
    /**
     * Returns suite that was used to load specifications.
     *
     * @return Suite
     */
    public function getSuite();
}

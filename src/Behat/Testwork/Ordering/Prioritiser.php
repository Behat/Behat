<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Ordering;

use Behat\Testwork\Specification\SpecificationIterator;

/**
 * Algorithm for prioritising Specification execution
 *
 * @author Ciaran McNulty <mail@ciaranmcnulty.com>
 */
interface Prioritiser
{
    /**
     * @param SpecificationIterator[] $scenarioIterators
     * @return SpecificationIterator[]
     */
    public function prioritise(array $scenarioIterators);

    /**
     * @return string
     */
    public function getName();
}

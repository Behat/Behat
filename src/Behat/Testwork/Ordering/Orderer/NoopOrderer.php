<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Ordering\Orderer;

use Behat\Testwork\Specification\SpecificationIterator;

/**
 * Null implementation of Orderer that does no ordering
 *
 * @author Ciaran McNulty <mail@ciaranmcnulty.com>
 */
final class NoopOrderer implements Orderer
{

    /**
     * @param SpecificationIterator[] $scenarioIterators
     * @return SpecificationIterator[]
     */
    public function order(array $scenarioIterators)
    {
        return $scenarioIterators;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'null';
    }
}

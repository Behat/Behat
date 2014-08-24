<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Ordering\Prioritiser;

use Behat\Testwork\Ordering\Prioritiser;

/**
 * Null implementation of Prioritiser that does no prioritisation
 *
 * @author Ciaran McNulty <mail@ciaranmcnulty.com>
 */
class NullPrioritiser implements Prioritiser
{

    /**
     * @param SpecificationIterator[] $scenarioIterators
     * @return SpecificationIterator[]
     */
    public function prioritise(array $scenarioIterators)
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

<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Ordering\Orderer;

use Behat\Testwork\Specification\SpecificationArrayIterator;
use Behat\Testwork\Specification\SpecificationIterator;

/**
 * Prioritises Suites and Features into reverse order
 *
 * @author Ciaran McNulty <mail@ciaranmcnulty.com>
 */
final class ReverseOrderer implements Orderer
{
    /**
     * @param SpecificationIterator[] $scenarioIterators
     * @return SpecificationIterator[]
     */
    public function order(array $scenarioIterators)
    {
        $orderedFeatures = $this->orderFeatures($scenarioIterators);
        $orderedSuites = $this->orderSuites($orderedFeatures);

        return $orderedSuites;
    }

    /**
     * @param array $scenarioIterators
     * @return array
     */
    private function orderFeatures(array $scenarioIterators)
    {
        $orderedSuites = array();

        foreach ($scenarioIterators as $scenarioIterator) {
            $orderedSpecifications = array_reverse(iterator_to_array($scenarioIterator));
            $orderedSuites[] = new SpecificationArrayIterator(
                $scenarioIterator->getSuite(),
                $orderedSpecifications
            );
        }

        return $orderedSuites;
    }

    /**
     * @param $orderedSuites
     * @return array
     */
    private function orderSuites($orderedSuites)
    {
        return array_reverse($orderedSuites);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'reverse';
    }
}

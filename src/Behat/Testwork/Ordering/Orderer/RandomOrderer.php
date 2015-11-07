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

/**
 * Prioritises Suites and Features into random order
 *
 * @author Ciaran McNulty <mail@ciaranmcnulty.com>
 */
final class RandomOrderer implements Orderer
{
    /**
     * @param SpecificationIterator[] $scenarioIterators
     * @return SpecificationIterator[]
     */
    public function order(array $scenarioIterators)
    {
        $orderedFeatures = $this->orderFeatures($scenarioIterators);
        shuffle($orderedFeatures);

        return $orderedFeatures;
    }

    /**
     * @param array $scenarioIterators
     * @return array
     */
    private function orderFeatures(array $scenarioIterators)
    {
        $orderedSuites = array();

        foreach ($scenarioIterators as $scenarioIterator) {
            $orderedSpecifications = iterator_to_array($scenarioIterator);
            shuffle($orderedSpecifications);
            $orderedSuites[] = new SpecificationArrayIterator(
                $scenarioIterator->getSuite(),
                $orderedSpecifications
            );
        }

        return $orderedSuites;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'random';
    }
}

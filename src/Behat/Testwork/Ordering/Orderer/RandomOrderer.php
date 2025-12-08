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
 * Prioritises Suites and Features into random order.
 *
 * @author Ciaran McNulty <mail@ciaranmcnulty.com>
 */
final class RandomOrderer implements Orderer
{
    /**
     * @template T
     *
     * @param SpecificationIterator<T>[] $scenarioIterators
     *
     * @return list<SpecificationIterator<T>>
     */
    public function order(array $scenarioIterators): array
    {
        $orderedFeatures = $this->orderFeatures($scenarioIterators);
        shuffle($orderedFeatures);

        return $orderedFeatures;
    }

    /**
     * @template T
     *
     * @param SpecificationIterator<T>[] $scenarioIterators
     *
     * @return list<SpecificationIterator<T>>
     */
    private function orderFeatures(array $scenarioIterators): array
    {
        $orderedSuites = [];

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

    public function getName(): string
    {
        return 'random';
    }
}

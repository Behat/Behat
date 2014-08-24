<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Priority\Prioritiser;

use Behat\Behat\Tester\Priority\Prioritiser;
use Behat\Testwork\Specification\SpecificationArrayIterator;

/**
 * Prioritises Suites and Features into random order
 *
 * @author Ciaran McNulty <mail@ciaranmcnulty.com>
 */
class RandomPrioritiser implements Prioritiser
{
    /**
     * @param SpecificationIterator[] $scenarioIterators
     * @return SpecificationIterator[]
     */
    public function prioritise(array $scenarioIterators)
    {
        $prioritisedFeatures = $this->prioritiseFeatures($scenarioIterators);
        shuffle($prioritisedFeatures);

        return $prioritisedFeatures;
    }

    /**
     * @param array $scenarioIterators
     * @return array
     */
    private function prioritiseFeatures(array $scenarioIterators)
    {
        $prioritisedSuites = array();

        foreach ($scenarioIterators as $scenarioIterator) {
            $prioritisedSpecifications = iterator_to_array($scenarioIterator);
            shuffle($prioritisedSpecifications);
            $prioritisedSuites[] = new SpecificationArrayIterator(
                $scenarioIterator->getSuite(),
                $prioritisedSpecifications
            );
        }

        return $prioritisedSuites;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'random';
    }
}

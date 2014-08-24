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
use Behat\Testwork\Specification\SpecificationArrayIterator;

/**
 * Prioritises Suites and Features into reverse order
 *
 * @author Ciaran McNulty <mail@ciaranmcnulty.com>
 */
class ReversePrioritiser implements Prioritiser
{
    /**
     * @param SpecificationIterator[] $scenarioIterators
     * @return SpecificationIterator[]
     */
    public function prioritise(array $scenarioIterators)
    {
        $prioritisedFeatures = $this->prioritiseFeatures($scenarioIterators);
        $prioritisedSuites = $this->prioritiseSuites($prioritisedFeatures);

        return $prioritisedSuites;
    }

    /**
     * @param array $scenarioIterators
     * @return array
     */
    private function prioritiseFeatures(array $scenarioIterators)
    {
        $prioritisedSuites = array();

        foreach ($scenarioIterators as $scenarioIterator) {
            $prioritisedSpecifications = array_reverse(iterator_to_array($scenarioIterator));
            $prioritisedSuites[] = new SpecificationArrayIterator(
                $scenarioIterator->getSuite(),
                $prioritisedSpecifications
            );
        }

        return $prioritisedSuites;
    }

    /**
     * @param $prioritisedSuites
     * @return array
     */
    private function prioritiseSuites($prioritisedSuites)
    {
        return array_reverse($prioritisedSuites);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'reverse';
    }
}

<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Specification;

use Behat\Testwork\Specification\Locator\SpecificationLocator;
use Behat\Testwork\Suite\Suite;

/**
 * Finds test specifications for provided suites using registered locators.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SpecificationFinder
{
    /**
     * @var SpecificationLocator[]
     */
    private $specificationLocators = array();

    /**
     * Registers specification locator.
     *
     * @param SpecificationLocator $locator
     */
    public function registerSpecificationLocator(SpecificationLocator $locator)
    {
        $this->specificationLocators[] = $locator;
    }

    /**
     * Returns array of strings representing examples of supported specification locators.
     *
     * @return string[]
     */
    public function getExampleLocators()
    {
        $examples = array();
        foreach ($this->specificationLocators as $locator) {
            $examples = array_merge($examples, $locator->getLocatorExamples());
        }

        return $examples;
    }

    /**
     * Finds all specifications for all provided suites matching provided locator and wraps them into a spec iterator.
     *
     * @param Suite[]     $suites
     * @param null|string $locator
     *
     * @return SpecificationIterator[]
     */
    public function findSuitesSpecifications(array $suites, $locator = null)
    {
        $iterators = array();
        foreach ($suites as $suite) {
            $iterators = array_merge($iterators, $this->findSuiteSpecifications($suite, $locator));
        }

        return $iterators;
    }

    /**
     * Creates suite specification iterator for provided locator.
     *
     * @param Suite       $suite
     * @param null|string $locator
     *
     * @return SpecificationIterator[]
     */
    private function findSuiteSpecifications(Suite $suite, $locator = null)
    {
        $iterators = array();
        foreach ($this->specificationLocators as $specificationLocator) {
            $iterators[] = $specificationLocator->locateSpecifications($suite, $locator);
        }

        return $iterators;
    }
}

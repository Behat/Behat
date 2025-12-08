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
 *
 * @template T
 */
final class SpecificationFinder
{
    /**
     * @var SpecificationLocator<T>[]
     */
    private $specificationLocators = [];

    /**
     * Registers specification locator.
     *
     * @param SpecificationLocator<T> $locator
     */
    public function registerSpecificationLocator(SpecificationLocator $locator): void
    {
        $this->specificationLocators[] = $locator;
    }

    /**
     * Returns array of strings representing examples of supported specification locators.
     *
     * @return string[]
     */
    public function getExampleLocators(): array
    {
        $examples = [];
        foreach ($this->specificationLocators as $locator) {
            $examples = array_merge($examples, $locator->getLocatorExamples());
        }

        return $examples;
    }

    /**
     * Finds all specifications for all provided suites matching provided locator and wraps them into a spec iterator.
     *
     * @param Suite[]     $suites
     *
     * @return list<SpecificationIterator<T>>
     */
    public function findSuitesSpecifications(array $suites, ?string $locator = null): array
    {
        $iterators = [];
        foreach ($suites as $suite) {
            $iterators = array_merge($iterators, $this->findSuiteSpecifications($suite, $locator));
        }

        return $iterators;
    }

    /**
     * Creates suite specification iterator for provided locator.
     *
     * @param string|null $locator
     *
     * @return list<SpecificationIterator<T>>
     */
    private function findSuiteSpecifications(Suite $suite, $locator = null): array
    {
        $iterators = [];
        foreach ($this->specificationLocators as $specificationLocator) {
            $iterators[] = $specificationLocator->locateSpecifications($suite, $locator);
        }

        return $iterators;
    }
}

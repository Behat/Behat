<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Gherkin\Specification\Locator;

use Behat\Behat\Gherkin\Specification\LazyFeatureIterator;
use Behat\Gherkin\Gherkin;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Specification\Locator\SpecificationLocator;
use Behat\Testwork\Specification\NoSpecificationsIterator;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Suite\Suite;

/**
 * Loads gherkin features using a file with the list of scenarios.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @implements SpecificationLocator<FeatureNode>
 */
final class FilesystemScenariosListLocator implements SpecificationLocator
{
    /**
     * Initializes locator.
     */
    public function __construct(
        private readonly Gherkin $gherkin,
    ) {
    }

    public function getLocatorExamples(): array
    {
        return ['a scenarios list file <comment>(*.scenarios)</comment>.'];
    }

    public function locateSpecifications(Suite $suite, $locator): SpecificationIterator
    {
        if (null === $locator || !is_file($locator) || 'scenarios' !== pathinfo($locator, PATHINFO_EXTENSION)) {
            return new NoSpecificationsIterator($suite);
        }

        $scenarios = explode("\n", trim(file_get_contents($locator)));

        return new LazyFeatureIterator($suite, $this->gherkin, $scenarios);
    }
}

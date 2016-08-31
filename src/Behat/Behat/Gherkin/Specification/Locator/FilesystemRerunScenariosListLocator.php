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
use Behat\Testwork\Specification\Locator\SpecificationLocator;
use Behat\Testwork\Specification\NoSpecificationsIterator;
use Behat\Testwork\Suite\Suite;

/**
 * Loads gherkin features using a file with the list of scenarios.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class FilesystemRerunScenariosListLocator implements SpecificationLocator
{
    /**
     * @var Gherkin
     */
    private $gherkin;

    /**
     * Initializes locator.
     *
     * @param Gherkin $gherkin
     */
    public function __construct(Gherkin $gherkin)
    {
        $this->gherkin = $gherkin;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocatorExamples()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function locateSpecifications(Suite $suite, $locator)
    {
        if (!is_file($locator) || 'rerun' !== pathinfo($locator, PATHINFO_EXTENSION)) {
            return new NoSpecificationsIterator($suite);
        }

        $scenarios = json_decode(trim(file_get_contents($locator)), true);
        if (empty($scenarios) || empty($scenarios[$suite->getName()])) {
            return new NoSpecificationsIterator($suite);
        }

        return new LazyFeatureIterator($suite, $this->gherkin, $scenarios[$suite->getName()]);
    }
}

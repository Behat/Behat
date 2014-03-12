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
final class FilesystemScenariosListLocator implements SpecificationLocator
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
        return array("a scenarios list file <comment>(*.scenarios)</comment>.");
    }

    /**
     * {@inheritdoc}
     */
    public function locateSpecifications(Suite $suite, $locator)
    {
        if (!is_file($locator) || 'scenarios' !== pathinfo($locator, PATHINFO_EXTENSION)) {
            return new NoSpecificationsIterator($suite);
        }

        $scenarios = explode("\n", trim(file_get_contents($locator)));

        return new LazyFeatureIterator($suite, $this->gherkin, $scenarios);
    }
}

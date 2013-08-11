<?php

namespace Behat\Behat\Features\Loader;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Features\Loader\LoaderInterface;
use Behat\Behat\Suite\Suite;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Gherkin\Filter\PathsFilter;
use Behat\Gherkin\Gherkin;
use Behat\Gherkin\Node\FeatureNode;

/**
 * Gherkin loader interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class GherkinLoader implements LoaderInterface
{
    /**
     * @var Gherkin
     */
    private $gherkin;

    /**
     * Initializes loader.
     *
     * @param Gherkin $gherkin
     */
    public function __construct(Gherkin $gherkin)
    {
        $this->gherkin = $gherkin;
    }

    /**
     * Checks if loader supports provided suite & locator.
     *
     * @param SuiteInterface $suite
     * @param string         $locator
     *
     * @return Boolean
     */
    public function supports(SuiteInterface $suite, $locator)
    {
        return $suite instanceof Suite;
    }

    /**
     * Loads features using provided suite & locator.
     *
     * @param SuiteInterface $suite
     * @param string         $locator
     *
     * @return FeatureNode[]
     */
    public function load(SuiteInterface $suite, $locator)
    {
        $filters = $suite->getFeatureFilters();
        $filters[] = new PathsFilter($suite->getFeatureLocators());

        if ($locator) {
            return $this->gherkin->load($locator, $filters);
        }

        $features = array();
        foreach ($suite->getFeatureLocators() as $suiteLocator) {
            $features = array_merge($features, $this->gherkin->load($suiteLocator, $filters));
        }

        return $features;
    }
}

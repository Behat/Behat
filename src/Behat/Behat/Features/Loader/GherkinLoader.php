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
use Behat\Behat\Suite\GherkinSuite;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Gherkin\Filter\PathsFilter;
use Behat\Gherkin\Gherkin;
use Behat\Gherkin\Node\FeatureNode;

/**
 * Gherkin suites loader.
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
        return $suite instanceof GherkinSuite;
    }

    /**
     * Loads features using provided suite & locator.
     *
     * @param GherkinSuite $suite
     * @param string       $locator
     *
     * @return FeatureNode[]
     */
    public function load(SuiteInterface $suite, $locator)
    {
        $filters = $suite->getFeatureFilters();

        if ($locator) {
            $filters[] = new PathsFilter($suite->getFeatureLocators());

            return $this->gherkin->load($locator, $filters);
        }

        $features = array();
        foreach ($suite->getFeatureLocators() as $suiteLocator) {
            $features = array_merge($features, $this->gherkin->load($suiteLocator, $filters));
        }

        return $features;
    }
}

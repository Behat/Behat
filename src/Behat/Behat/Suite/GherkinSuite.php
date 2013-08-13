<?php

namespace Behat\Behat\Suite;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Gherkin\Filter\FilterInterface;

/**
 * Gherkin suite.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class GherkinSuite implements SuiteInterface
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string[]
     */
    private $featureLocators = array();
    /**
     * @var FilterInterface[]
     */
    private $featureFilters = array();
    /**
     * @var string[]
     */
    private $contextClasses = array();
    /**
     * @var array
     */
    private $parameters = array();

    /**
     * Initializes suite.
     *
     * @param string $name
     * @param array  $featureLocators
     * @param array  $featureFilters
     * @param array  $contextClasses
     * @param array  $parameters
     */
    public function __construct(
        $name,
        array $featureLocators,
        array $featureFilters,
        array $contextClasses,
        array $parameters
    )
    {
        $this->id = mt_rand();
        $this->name = $name;
        $this->featureLocators = $featureLocators;
        $this->featureFilters = $featureFilters;
        $this->contextClasses = $contextClasses;
        $this->parameters = $parameters;
    }

    /**
     * Returns unique ID of this suite.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns suite name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns list of feature locators.
     * Conventionally, locators that are not paths in the local filesystem should start with `@` sign
     * followed by the ID of a remote storage.
     *
     * @return string[]
     */
    public function getFeatureLocators()
    {
        return $this->featureLocators;
    }

    /**
     * Returns feature filters.
     *
     * @return FilterInterface[]
     */
    public function getFeatureFilters()
    {
        return $this->featureFilters;
    }

    /**
     * Returns context class names.
     *
     * @return string[]
     */
    public function getContextClasses()
    {
        return $this->contextClasses;
    }

    /**
     * Returns parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}

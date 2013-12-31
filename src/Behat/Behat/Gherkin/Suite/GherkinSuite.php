<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Gherkin\Suite;

use Behat\Gherkin\Filter\FilterInterface;
use Behat\Testwork\Suite\Exception\ParameterNotFoundException;
use Behat\Testwork\Suite\Suite;

/**
 * Gherkin suite.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class GherkinSuite implements Suite
{
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
     * @var array
     */
    private $settings = array();
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
     * @param array  $settings
     * @param array  $parameters
     */
    public function __construct(
        $name,
        array $featureLocators,
        array $featureFilters,
        array $settings,
        array $parameters
    ) {
        $this->name = $name;
        $this->featureLocators = $featureLocators;
        $this->featureFilters = $featureFilters;
        $this->settings = $settings;
        $this->parameters = $parameters;
    }

    /**
     * Returns unique suite name.
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
     * followed by the ID of a remote storage (f.e.: @github:/path/to/some.feature)
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
     * Returns suite settings.
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Checks if a setting with provided name exists.
     *
     * @param string $key
     *
     * @return Boolean
     */
    public function hasSetting($key)
    {
        return isset($this->settings[$key]);
    }

    /**
     * Returns setting value by its key.
     *
     * @param string $key
     *
     * @return mixed
     *
     * @throws ParameterNotFoundException If setting is not set
     */
    public function getSetting($key)
    {
        if (!$this->hasSetting($key)) {
            throw new ParameterNotFoundException(sprintf(
                '`%s` suite does not have a `%s` setting.',
                $this->getName(),
                $key
            ), $this->getName(), $key);
        }

        return $this->settings[$key];
    }

    /**
     * Returns custom parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Checks if parameter with provided name exists.
     *
     * @param string $key
     *
     * @return Boolean
     */
    public function hasParameter($key)
    {
        return isset($this->parameters[$key]);
    }

    /**
     * Returns parameter value by its key.
     *
     * @param string $key
     *
     * @return mixed
     *
     * @throws ParameterNotFoundException If parameter is not set
     */
    public function getParameter($key)
    {
        if (!$this->hasParameter($key)) {
            throw new ParameterNotFoundException(sprintf(
                '`%s` suite does not have a `%s`.',
                $this->getName(),
                $key
            ), $this->getName(), $key);
        }

        return $this->parameters[$key];
    }
}

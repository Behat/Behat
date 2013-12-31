<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Gherkin\Suite\Generator;

use Behat\Behat\Gherkin\Suite\GherkinSuite;
use Behat\Gherkin\Filter\FilterInterface;
use Behat\Gherkin\Filter\NameFilter;
use Behat\Gherkin\Filter\RoleFilter;
use Behat\Gherkin\Filter\TagFilter;
use Behat\Testwork\Suite\Exception\SuiteConfigurationException;
use Behat\Testwork\Suite\Generator\SuiteGenerator;
use Behat\Testwork\Suite\Suite;

/**
 * Gherkin suite generator.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class GherkinSuiteGenerator implements SuiteGenerator
{
    /**
     * @var array
     */
    private $defaultSettings = array();

    /**
     * Initializes suite generator.
     *
     * @param array $defaultSettings
     */
    public function __construct(array $defaultSettings = array())
    {
        $this->defaultSettings = $defaultSettings;
    }

    /**
     * Checks if generator support provided suite type and settings.
     *
     * @param string $type
     * @param array  $settings
     *
     * @return Boolean
     */
    public function supportsTypeAndSettings($type, array $settings)
    {
        return 'gherkin' === $type;
    }

    /**
     * Generate suite with provided name, settings and parameters.
     *
     * @param string $suiteName
     * @param array  $settings
     * @param array  $parameters
     *
     * @return Suite
     */
    public function generateSuite($suiteName, array $settings, array $parameters)
    {
        $settings = $this->normalizeSettings($settings);

        return new GherkinSuite(
            $suiteName,
            $this->getFeatureLocators($suiteName, $settings),
            $this->getFeatureFilters($suiteName, $settings),
            $settings,
            $parameters
        );
    }

    /**
     * Returns list of locators from suite settings.
     *
     * @param string $suiteName
     * @param array  $settings
     *
     * @return string[]
     *
     * @throws SuiteConfigurationException If settings do not have `paths` nor `path`
     */
    protected function getFeatureLocators($suiteName, array $settings)
    {
        if (!isset($settings['paths'])) {
            throw new SuiteConfigurationException(sprintf(
                'Gherkin suites should have either `paths` or `path` setting set, but `%s` has none.',
                $suiteName
            ), $suiteName);
        }

        return $settings['paths'];
    }

    /**
     * Returns list of filters from suite settings.
     *
     * @param string $suiteName
     * @param array  $settings
     *
     * @return FilterInterface[]
     *
     * @throws SuiteConfigurationException If unknown filter type provided
     */
    protected function getFeatureFilters($suiteName, array $settings)
    {
        if (!isset($settings['filters'])) {
            return array();
        }

        $filters = array();
        foreach ($settings['filters'] as $type => $filterString) {
            switch ($type) {
                case 'role':
                    $filters[] = new RoleFilter($filterString);
                    break;
                case 'name':
                    $filters[] = new NameFilter($filterString);
                    break;
                case 'tags':
                    $filters[] = new TagFilter($filterString);
                    break;
                default:
                    throw new SuiteConfigurationException(sprintf(
                        '`%s` filter is not supported by the `%s` suite. Supported types are %s.',
                        $type,
                        $suiteName,
                        implode(', ', array('`role`', '`name`', '`tags`'))
                    ), $suiteName);
            }
        }

        return $filters;
    }

    /**
     * Normalizes settings array.
     *
     * Sets default setting if none provided.
     * Also merges `path` into `paths` array.
     *
     * @param array $settings
     *
     * @return array
     */
    private function normalizeSettings(array $settings)
    {
        if (isset($settings['path'])) {
            if (!isset($settings['paths'])) {
                $settings['paths'] = array($settings['path']);
            }
            unset($settings['path']);
        }

        return array_merge($this->defaultSettings, $settings);
    }
}

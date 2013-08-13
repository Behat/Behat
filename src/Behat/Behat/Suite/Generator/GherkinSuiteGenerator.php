<?php

namespace Behat\Behat\Suite\Generator;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Suite\Generator\GeneratorInterface;
use Behat\Behat\Suite\GherkinSuite;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Gherkin\Filter\FilterInterface;
use Behat\Gherkin\Filter\NameFilter;
use Behat\Gherkin\Filter\RoleFilter;
use Behat\Gherkin\Filter\TagFilter;
use InvalidArgumentException;

/**
 * Gherkin suite generator.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class GherkinSuiteGenerator implements GeneratorInterface
{
    /**
     * Checks if generator support provided suite type and parameters.
     *
     * @param string $type
     * @param array  $parameters
     *
     * @return Boolean
     */
    public function supports($type, array $parameters)
    {
        return 'gherkin' === $type;
    }

    /**
     * Generate suite with provided name and parameters.
     *
     * @param string $suiteName
     * @param array  $parameters
     *
     * @return SuiteInterface
     */
    public function generate($suiteName, array $parameters)
    {
        $featureLocators = $this->getFeatureLocators($suiteName, $parameters);
        $featureFilters = $this->getFeatureFilters($suiteName, $parameters);
        $contextClasses = $this->getContextClasses($suiteName, $parameters);

        return new GherkinSuite($suiteName, $featureLocators, $featureFilters, $contextClasses, $parameters);
    }

    /**
     * Returns list of locators from suite parameters.
     *
     * @param string $suiteName
     * @param array  $parameters
     *
     * @return string[]
     *
     * @throws InvalidArgumentException If parameters do not have `paths` nor `path`
     */
    protected function getFeatureLocators($suiteName, array $parameters)
    {
        if (isset($parameters['paths'])) {
            return $parameters['paths'];
        }

        if (!isset($parameters['path'])) {
            throw new InvalidArgumentException(sprintf(
                'Suite "%s" should have either "path" or "paths" option set.',
                $suiteName
            ));
        }

        return array($parameters['path']);
    }

    /**
     * Returns list of filters from suite parameters.
     *
     * @param string $suiteName
     * @param array  $parameters
     *
     * @return FilterInterface[]
     *
     * @throws InvalidArgumentException If unknown filter type provided
     */
    protected function getFeatureFilters($suiteName, array $parameters)
    {
        if (!isset($parameters['filters'])) {
            return array();
        }

        $filters = array();
        foreach ($parameters['filters'] as $type => $filterString) {
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
                    throw new InvalidArgumentException(sprintf(
                        'Filter type "%s" is not supported by suite "%s". Supported types are %s.',
                        $type,
                        $suiteName,
                        implode(', ', array('"role"', '"name"', '"tags"'))
                    ));
            }
        }

        return $filters;
    }

    /**
     * Returns list of context classes from suite parameters.
     *
     * @param string $suiteName
     * @param array  $parameters
     *
     * @return string[]
     */
    protected function getContextClasses($suiteName, array $parameters)
    {
        if (isset($parameters['context'])) {
            return array($parameters['context']);
        }

        return isset($parameters['contexts']) ? $parameters['contexts'] : array();
    }
}

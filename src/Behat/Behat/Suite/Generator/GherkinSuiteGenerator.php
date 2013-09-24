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
    public function supports($type, array $settings)
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
     * @return SuiteInterface
     */
    public function generate($suiteName, array $settings, array $parameters)
    {
        $settings = $this->normalizeSettings($settings);

        return new GherkinSuite(
            $suiteName,
            $this->getFeatureLocators($suiteName, $settings),
            $this->getFeatureFilters($suiteName, $settings),
            $this->getContextClasses($suiteName, $settings),
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
     * @throws InvalidArgumentException If settings do not have `paths` nor `path`
     */
    protected function getFeatureLocators($suiteName, array $settings)
    {
        if (!isset($settings['paths'])) {
            throw new InvalidArgumentException(sprintf(
                'Suite "%s" should have either "paths" or "path" setting set.',
                $suiteName
            ));
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
     * @throws InvalidArgumentException If unknown filter type provided
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
     * Returns list of context classes from suite settings.
     *
     * @param string $suiteName
     * @param array  $settings
     *
     * @return string[]
     */
    protected function getContextClasses($suiteName, array $settings)
    {
        return isset($settings['contexts']) ? $settings['contexts'] : array();
    }

    /**
     * Normalizes settings array.
     *
     * Sets default setting if none provided.
     * Also merges `path` and `context` values into `paths` and `contexts` array.
     *
     * @param array $settings
     *
     * @return array
     */
    private function normalizeSettings(array $settings)
    {
        if (isset($settings['context'])) {
            if (!isset($settings['contexts'])) {
                $settings['contexts'] = array($settings['context']);
            }
            unset($settings['context']);
        }

        if (isset($settings['path'])) {
            if (!isset($settings['paths'])) {
                $settings['paths'] = array($settings['path']);
            }
            unset($settings['path']);
        }

        return array_merge($this->defaultSettings, $settings);
    }
}

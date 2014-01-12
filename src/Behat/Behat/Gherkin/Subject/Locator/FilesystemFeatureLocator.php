<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Gherkin\Subject\Locator;

use Behat\Behat\Gherkin\Subject\LazyFeatureIterator;
use Behat\Gherkin\Filter\FilterInterface;
use Behat\Gherkin\Filter\NameFilter;
use Behat\Gherkin\Filter\PathsFilter;
use Behat\Gherkin\Filter\RoleFilter;
use Behat\Gherkin\Filter\TagFilter;
use Behat\Gherkin\Gherkin;
use Behat\Testwork\Subject\EmptySubjectIterator;
use Behat\Testwork\Subject\Locator\SubjectLocator;
use Behat\Testwork\Suite\Exception\SuiteConfigurationException;
use Behat\Testwork\Suite\Suite;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

/**
 * Gherkin feature loader.
 *
 * Loads gherkin features from the filesystem using gherkin parser.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FilesystemFeatureLocator implements SubjectLocator
{
    /**
     * @var Gherkin
     */
    private $gherkin;
    /**
     * @var string
     */
    private $basePath;

    /**
     * Initializes loader.
     *
     * @param Gherkin $gherkin
     * @param string  $basePath
     */
    public function __construct(Gherkin $gherkin, $basePath)
    {
        $this->gherkin = $gherkin;
        $this->basePath = $basePath;
    }

    /**
     * Loads feature iterator using provided suite & locator.
     *
     * @param Suite  $suite
     * @param string $locator
     *
     * @return LazyFeatureIterator
     */
    public function locateSubjects(Suite $suite, $locator)
    {
        if (!$suite->hasSetting('paths')) {
            return new EmptySubjectIterator($suite);
        }

        $suiteLocators = $suite->getSetting('paths');
        $suiteFilters = $this->getFeatureFilters($suite);

        if ($locator) {
            $suiteFilters[] = new PathsFilter($suiteLocators);

            return new LazyFeatureIterator($suite, $this->gherkin, $this->findFeatureFiles($locator), $suiteFilters);
        }

        $featurePaths = array();
        foreach ($suiteLocators as $suiteLocator) {
            $featurePaths = array_merge($featurePaths, $this->findFeatureFiles($suiteLocator));
        }

        return new LazyFeatureIterator($suite, $this->gherkin, $featurePaths, $suiteFilters);
    }

    /**
     * Returns list of filters from suite settings.
     *
     * @param Suite $suite
     *
     * @return FilterInterface[]
     */
    protected function getFeatureFilters(Suite $suite)
    {
        if (!$suite->hasSetting('filters') || !is_array($suite->getSetting('filters'))) {
            return array();
        }

        $filters = array();
        foreach ($suite->getSetting('filters') as $type => $filterString) {
            $filters[] = $this->createFilter($type, $filterString, $suite);
        }

        return $filters;
    }

    /**
     * Creates filter of provided type.
     *
     * @param string $type
     * @param string $filterString
     * @param Suite  $suite
     *
     * @return FilterInterface
     *
     * @throws SuiteConfigurationException If filter type is not recognised
     */
    protected function createFilter($type, $filterString, Suite $suite)
    {
        if ('role' === $type) {
            return new RoleFilter($filterString);
        }

        if ('name' === $type) {
            return new NameFilter($filterString);
        }

        if ('tags' === $type) {
            return new TagFilter($filterString);
        }

        throw new SuiteConfigurationException(sprintf(
            '`%s` filter is not supported by the `%s` suite. Supported types are %s.',
            $type,
            $suite->getName(),
            implode(', ', array('`role`', '`name`', '`tags`'))
        ), $suite->getName());
    }

    /**
     * Loads feature files paths from provided path.
     *
     * @param string $path
     *
     * @return string[]
     */
    protected function findFeatureFiles($path)
    {
        $absolutePath = $this->findAbsolutePath($path);

        if (!$absolutePath) {
            return array($path);
        }

        if (is_file($absolutePath)) {
            return array($absolutePath);
        }

        $iterator = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($absolutePath)
            ), '/^.+\.feature$/i',
            RegexIterator::MATCH
        );
        $paths = array_map('strval', iterator_to_array($iterator));
        uasort($paths, 'strnatcasecmp');

        return $paths;
    }

    /**
     * Finds absolute path for provided relative (relative to base features path).
     *
     * @param string $path Relative path
     *
     * @return string
     */
    protected function findAbsolutePath($path)
    {
        if (is_file($path) || is_dir($path)) {
            return realpath($path);
        }

        if (null === $this->basePath) {
            return false;
        }

        if (is_file($this->basePath . DIRECTORY_SEPARATOR . $path)
            || is_dir($this->basePath . DIRECTORY_SEPARATOR . $path)
        ) {
            return realpath($this->basePath . DIRECTORY_SEPARATOR . $path);
        }

        return false;
    }
}

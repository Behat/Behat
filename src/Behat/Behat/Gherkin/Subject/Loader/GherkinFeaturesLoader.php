<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Gherkin\Subject\Loader;

use Behat\Behat\Gherkin\Subject\LazyFeatures;
use Behat\Behat\Gherkin\Suite\GherkinSuite;
use Behat\Gherkin\Filter\PathsFilter;
use Behat\Gherkin\Gherkin;
use Behat\Testwork\Subject\Loader\SubjectsLoader;
use Behat\Testwork\Suite\Suite;
use Symfony\Component\Finder\Finder;

/**
 * Gherkin features loader.
 *
 * Loads gherkin features from the filesystem using gherkin parser.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class GherkinFeaturesLoader implements SubjectsLoader
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
     * Checks if loader supports provided suite & locator.
     *
     * @param Suite  $suite
     * @param string $locator
     *
     * @return Boolean
     */
    public function supportsSuiteAndLocator(Suite $suite, $locator)
    {
        return $suite instanceof GherkinSuite;
    }

    /**
     * Loads features using provided suite & locator.
     *
     * @param GherkinSuite $suite
     * @param string       $locator
     *
     * @return LazyFeatures
     */
    public function loadTestSubjects(Suite $suite, $locator)
    {
        $filters = $suite->getFeatureFilters();

        if ($locator) {
            $filters[] = new PathsFilter($suite->getFeatureLocators());

            return new LazyFeatures($suite, $this->gherkin, $this->findFeatureFiles($locator), $filters);
        }

        $featurePaths = array();
        foreach ($suite->getFeatureLocators() as $suiteLocator) {
            $featurePaths = array_merge($featurePaths, $this->findFeatureFiles($suiteLocator));
        }

        return new LazyFeatures($suite, $this->gherkin, $featurePaths, $filters);
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

        $finder = new Finder();
        $iterator = $finder->files()->name('*.feature')->sortByName()->in($absolutePath);

        return array_map('strval', iterator_to_array($iterator));
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
            || is_dir($this->basePath . DIRECTORY_SEPARATOR . $path)) {
            return realpath($this->basePath . DIRECTORY_SEPARATOR . $path);
        }

        return false;
    }
}

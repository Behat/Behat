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
use Behat\Gherkin\Filter\PathsFilter;
use Behat\Gherkin\Gherkin;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Testwork\Specification\Locator\SpecificationLocator;
use Behat\Testwork\Specification\NoSpecificationsIterator;
use Behat\Testwork\Specification\SpecificationIterator;
use Behat\Testwork\Suite\Exception\SuiteConfigurationException;
use Behat\Testwork\Suite\Suite;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

/**
 * Loads gherkin features from the filesystem using gherkin parser.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @implements SpecificationLocator<FeatureNode>
 */
final class FilesystemFeatureLocator implements SpecificationLocator
{
    /**
     * Initializes loader.
     *
     * @param string  $basePath
     */
    public function __construct(
        private readonly Gherkin $gherkin,
        private $basePath,
    ) {
    }

    public function getLocatorExamples(): array
    {
        return [
            'a dir <comment>(features/)</comment>',
            'a feature <comment>(*.feature)</comment>',
            'a scenario at specific line <comment>(*.feature:10)</comment>.',
            'all scenarios at or after a specific line <comment>(*.feature:10-*)</comment>.',
            'all scenarios at a line within a specific range <comment>(*.feature:10-20)</comment>.',
        ];
    }

    public function locateSpecifications(Suite $suite, $locator): SpecificationIterator
    {
        if (!$suite->hasSetting('paths')) {
            return new NoSpecificationsIterator($suite);
        }

        $suiteLocators = $this->getSuitePaths($suite);

        if ($locator) {
            $filters = [new PathsFilter($suiteLocators)];

            return new LazyFeatureIterator($suite, $this->gherkin, $this->findFeatureFiles($locator), $filters);
        }

        $featurePaths = [];
        foreach ($suiteLocators as $suiteLocator) {
            $featurePaths = array_merge($featurePaths, $this->findFeatureFiles($suiteLocator));
        }

        return new LazyFeatureIterator($suite, $this->gherkin, $featurePaths);
    }

    /**
     * Returns array of feature paths configured for the provided suite.
     *
     * @return string[]
     *
     * @throws SuiteConfigurationException If `paths` setting is not an array
     */
    private function getSuitePaths(Suite $suite)
    {
        if (!is_array($suite->getSetting('paths'))) {
            throw new SuiteConfigurationException(
                sprintf(
                    '`paths` setting of the "%s" suite is expected to be an array, %s given.',
                    $suite->getName(),
                    gettype($suite->getSetting('paths'))
                ),
                $suite->getName()
            );
        }

        return $suite->getSetting('paths');
    }

    /**
     * Loads feature files paths from provided path.
     *
     * @param string $path
     *
     * @return list<string>
     */
    private function findFeatureFiles($path): array
    {
        $absolutePath = $this->findAbsolutePath($path);

        if (!$absolutePath) {
            return [$path];
        }

        if (is_file($absolutePath)) {
            return [$absolutePath];
        }

        $iterator = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $absolutePath,
                    RecursiveDirectoryIterator::FOLLOW_SYMLINKS | RecursiveDirectoryIterator::SKIP_DOTS
                )
            ),
            '/^.+\.feature$/i',
            RegexIterator::MATCH
        );

        $paths = array_map('strval', iterator_to_array($iterator));
        uasort($paths, 'strnatcasecmp');

        return $paths;
    }

    /**
     * Finds absolute path for provided relative (relative to base features path).
     */
    private function findAbsolutePath(string $path): string|false
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

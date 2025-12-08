<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Gherkin\Suite\Setup;

use Behat\Testwork\Filesystem\FilesystemLogger;
use Behat\Testwork\Suite\Setup\SuiteSetup;
use Behat\Testwork\Suite\Suite;

/**
 * Sets up gherkin suite in the filesystem (creates feature folders).
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SuiteWithPathsSetup implements SuiteSetup
{
    /**
     * Initializes setup.
     *
     * @param string                $basePath
     */
    public function __construct(
        private $basePath,
        private readonly ?FilesystemLogger $logger = null,
    ) {
    }

    public function supportsSuite(Suite $suite): bool
    {
        return $suite->hasSetting('paths') && is_array($suite->getSetting('paths'));
    }

    public function setupSuite(Suite $suite): void
    {
        foreach ($suite->getSetting('paths') as $locator) {
            if (!str_starts_with((string) $locator, '@') && !is_dir($path = $this->locatePath($locator))) {
                $this->createFeatureDirectory($path);
            }
        }
    }

    /**
     * Creates feature directory.
     *
     * @param string $path
     */
    private function createFeatureDirectory($path): void
    {
        mkdir($path, 0777, true);

        if ($this->logger instanceof FilesystemLogger) {
            $this->logger->directoryCreated($path, 'place your *.feature files here');
        }
    }

    /**
     * Locates path from a relative one.
     *
     * @param string $path
     *
     * @return string
     */
    private function locatePath($path)
    {
        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        return $this->basePath . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * Returns whether the file path is an absolute path.
     *
     * @param string $file A file path
     */
    private function isAbsolutePath($file): bool
    {
        return $file[0] == '/' || $file[0] == '\\'
            || (
                strlen($file) > 3 && ctype_alpha($file[0])
                && $file[1] == ':'
                && ($file[2] == '\\' || $file[2] == '/')
            )
            || null !== parse_url($file, PHP_URL_SCHEME);
    }
}

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
     * @var string
     */
    private $basePath;
    /**
     * @var null|FilesystemLogger
     */
    private $logger;

    /**
     * Initializes setup.
     *
     * @param string                $basePath
     * @param null|FilesystemLogger $logger
     */
    public function __construct($basePath, FilesystemLogger $logger = null)
    {
        $this->basePath = $basePath;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsSuite(Suite $suite)
    {
        return $suite->hasSetting('paths') && is_array($suite->getSetting('paths'));
    }

    /**
     * {@inheritdoc}
     */
    public function setupSuite(Suite $suite)
    {
        foreach ($suite->getSetting('paths') as $locator) {
            if (0 !== strpos($locator, '@') && !is_dir($path = $this->locatePath($locator))) {
                $this->createFeatureDirectory($path);
            }
        }
    }

    /**
     * Creates feature directory.
     *
     * @param string $path
     */
    private function createFeatureDirectory($path)
    {
        mkdir($path, 0777, true);

        if ($this->logger) {
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
     *
     * @return bool
     */
    private function isAbsolutePath($file)
    {
        if ($file[0] == '/' || $file[0] == '\\'
            || (strlen($file) > 3 && ctype_alpha($file[0])
                && $file[1] == ':'
                && ($file[2] == '\\' || $file[2] == '/')
            )
            || null !== parse_url($file, PHP_URL_SCHEME)
        ) {
            return true;
        }

        return false;
    }
}

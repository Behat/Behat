<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Suite\Setup;

use Behat\Behat\Context\ClassGenerator\ContextClassGenerator;
use Behat\Behat\Context\Exception\ContextNotFoundException;
use Behat\Testwork\Filesystem\FilesystemLogger;
use Behat\Testwork\Suite\Setup\SuiteSetup;
use Behat\Testwork\Suite\Suite;
use Symfony\Component\ClassLoader\ClassLoader;

/**
 * Context-based suite setup.
 *
 * Generates classes for all suite contexts using autoloader.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SuiteWithContextsSetup implements SuiteSetup
{
    /**
     * @var ClassLoader
     */
    private $autoloader;
    /**
     * @var null|FilesystemLogger
     */
    private $logger;
    /**
     * @var ContextClassGenerator[]
     */
    private $classGenerators = array();

    /**
     * Initializes setup.
     *
     * @param ClassLoader           $autoloader
     * @param null|FilesystemLogger $logger
     */
    public function __construct(ClassLoader $autoloader, FilesystemLogger $logger = null)
    {
        $this->autoloader = $autoloader;
        $this->logger = $logger;
    }

    /**
     * Registers class generator.
     *
     * @param ContextClassGenerator $generator
     */
    public function registerClassGenerator(ContextClassGenerator $generator)
    {
        $this->classGenerators[] = $generator;
    }

    /**
     * Checks if setup supports provided suite.
     *
     * @param Suite $suite
     *
     * @return Boolean
     */
    public function supportsSuite(Suite $suite)
    {
        return $suite->hasSetting('contexts') && is_array($suite->getSetting('contexts'));
    }

    /**
     * Sets up provided suite.
     *
     * @param Suite $suite
     */
    public function setupSuite(Suite $suite)
    {
        foreach (array_keys($suite->getSetting('contexts')) as $classname) {
            if (class_exists($classname)) {
                continue;
            }

            $this->ensureContextDirectory($path = $this->findClassFile($classname));

            if ($content = $this->generateClass($suite, $classname)) {
                $this->createContextFile($path, $content);
            }
        }
    }

    /**
     * Creates context directory in the filesystem.
     *
     * @param string $path
     */
    protected function createContextDirectory($path)
    {
        mkdir($path, 0777, true);

        if ($this->logger) {
            $this->logger->directoryCreated($path, 'place your context classes here');
        }
    }

    /**
     * Creates context class file in the filesystem.
     *
     * @param string $path
     * @param string $content
     */
    protected function createContextFile($path, $content)
    {
        file_put_contents($path, $content);

        if ($this->logger) {
            $this->logger->fileCreated($path, 'place your definitions, transformations and hooks here');
        }
    }

    /**
     * Finds file to store a class.
     *
     * @param string $class
     *
     * @return string
     *
     * @throws ContextNotFoundException If class file could not be determined
     */
    protected function findClassFile($class)
    {
        list($classpath, $classname) = $this->findClasspathAndClassname($class);
        $classpath .= str_replace('_', DIRECTORY_SEPARATOR, $classname) . '.php';

        foreach ($this->autoloader->getPrefixes() as $prefix => $dirs) {
            if (0 === strpos($class, $prefix)) {
                return current($dirs) . DIRECTORY_SEPARATOR . $classpath;
            }
        }

        if ($dirs = $this->autoloader->getFallbackDirs()) {
            return current($dirs) . DIRECTORY_SEPARATOR . $classpath;
        }

        throw new ContextNotFoundException(sprintf(
            'Could not find where to put "%s" class. Have you configured autoloader properly?',
            $class
        ), $class);
    }

    /**
     * Generates class using registered class generators.
     *
     * @param Suite  $suite
     * @param string $classname
     *
     * @return null|string
     */
    final protected function generateClass(Suite $suite, $classname)
    {
        $content = null;
        foreach ($this->classGenerators as $generator) {
            if ($generator->supportsSuiteAndClassname($suite, $classname)) {
                $content = $generator->generateClass($suite, $classname);
            }
        }

        return $content;
    }

    /**
     * Ensures that directory for a classpath exists.
     *
     * @param string $classpath
     */
    private function ensureContextDirectory($classpath)
    {
        if (!is_dir(dirname($classpath))) {
            $this->createContextDirectory(dirname($classpath));
        }
    }

    /**
     * Finds classpath and classname from class.
     *
     * @param string $class
     *
     * @return array
     */
    private function findClasspathAndClassname($class)
    {
        if (false !== $pos = strrpos($class, '\\')) {
            // namespaced class name
            $classpath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos)) . DIRECTORY_SEPARATOR;
            $classname = substr($class, $pos + 1);

            return array($classpath, $classname);
        }

        // PEAR-like class name
        $classpath = null;
        $classname = $class;

        return array($classpath, $classname);
    }
}

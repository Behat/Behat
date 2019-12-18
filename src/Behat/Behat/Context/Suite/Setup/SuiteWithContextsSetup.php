<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Context\Suite\Setup;

use Behat\Behat\Context\ContextClass\ClassGenerator;
use Behat\Behat\Context\Exception\ContextNotFoundException;
use Behat\Testwork\Filesystem\FilesystemLogger;
use Behat\Testwork\Suite\Exception\SuiteConfigurationException;
use Behat\Testwork\Suite\Setup\SuiteSetup;
use Behat\Testwork\Suite\Suite;
use Composer\Autoload\ClassLoader;

/**
 * Generates classes for all contexts in the suite using autoloader.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class SuiteWithContextsSetup implements SuiteSetup
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
     * @var ClassGenerator[]
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
     * @param ClassGenerator $generator
     */
    public function registerClassGenerator(ClassGenerator $generator)
    {
        $this->classGenerators[] = $generator;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsSuite(Suite $suite)
    {
        return $suite->hasSetting('contexts');
    }

    /**
     * {@inheritdoc}
     */
    public function setupSuite(Suite $suite)
    {
        foreach ($this->getNormalizedContextClasses($suite) as $class) {
            if (class_exists($class)) {
                continue;
            }

            $this->ensureContextDirectory($path = $this->findClassFile($class));

            if ($content = $this->generateClass($suite, $class)) {
                $this->createContextFile($path, $content);
            }
        }
    }

    /**
     * Returns normalized context classes.
     *
     * @param Suite $suite
     *
     * @return string[]
     */
    private function getNormalizedContextClasses(Suite $suite)
    {
        return array_map(
            function ($context) {
                return is_array($context) ? current(array_keys($context)) : $context;
            },
            $this->getSuiteContexts($suite)
        );
    }

    /**
     * Returns array of context classes configured for the provided suite.
     *
     * @param Suite $suite
     *
     * @return string[]
     *
     * @throws SuiteConfigurationException If `contexts` setting is not an array
     */
    private function getSuiteContexts(Suite $suite)
    {
        $contexts = $suite->getSetting('contexts');

        if (!is_array($contexts)) {
            throw new SuiteConfigurationException(
                sprintf('`contexts` setting of the "%s" suite is expected to be an array, `%s` given.',
                    $suite->getName(),
                    gettype($contexts)
                ),
                $suite->getName()
            );
        }

        return $contexts;
    }

    /**
     * Creates context directory in the filesystem.
     *
     * @param string $path
     */
    private function createContextDirectory($path)
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
    private function createContextFile($path, $content)
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
    private function findClassFile($class)
    {
        list($classpath, $classname) = $this->findClasspathAndClass($class);
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
     * @param string $class
     *
     * @return null|string
     */
    private function generateClass(Suite $suite, $class)
    {
        $content = null;
        foreach ($this->classGenerators as $generator) {
            if ($generator->supportsSuiteAndClass($suite, $class)) {
                $content = $generator->generateClass($suite, $class);
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
    private function findClasspathAndClass($class)
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

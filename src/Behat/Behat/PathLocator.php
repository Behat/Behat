<?php

namespace Behat\Behat;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Finder\Finder;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat path locator.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PathLocator
{
    /**
     * Container object.
     *
     * @var     Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;
    /**
     * Provided in input features path.
     *
     * @var     string
     */
    private $inputPath;
    /**
     * Path tokens.
     *
     * @var     array
     */
    private $pathTokens = array(
        'BEHAT_WORK_PATH'   => '',  // working directory
        'BEHAT_CONFIG_PATH' => '',  // config file path
        'BEHAT_BASE_PATH'   => ''   // base path
    );
    /**
     * Work dir.
     *
     * @var     string
     */
    private $workPath;

    /**
     * Initializes path locator.
     *
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->workPath  = getcwd();
        $this->pathTokens['BEHAT_WORK_PATH'] = $this->workPath;
    }

    /**
     * Sets configuration path constant (token).
     *
     * @param   string  $configPath
     */
    public function setPathConstant($name, $path)
    {
        $this->pathTokens[$name] = $path;
    }

    /**
     * Returns current work path.
     *
     * @return  string
     */
    public function getWorkPath()
    {
        return $this->workPath;
    }

    /**
     * Returns configured features path.
     *
     * @return  string
     */
    public function getFeaturesPath()
    {
        if ($path = $this->container->getParameter('behat.paths.features')) {
            return $this->preparePath($path);
        }

        return null;
    }

    /**
     * Returns configured bootstrap path.
     *
     * @return  string
     */
    public function getBootstrapPath()
    {
        if ($path = $this->container->getParameter('behat.paths.bootstrap')) {
            return $this->preparePath($path);
        }

        return null;
    }

    /**
     * Returns configured output path.
     *
     * @param   string  $outputPath specified output path
     *
     * @return string|null
     */
    public function getOutputPath($outputPath = null)
    {
        if ($outputPath) {
            return $this->preparePath($outputPath);
        }

        return null;
    }

    /**
     * Locates base behat path.
     *
     * @param   string  $inputPath
     *
     * @return  string
     */
    public function locateBasePath($inputPath = null)
    {
        // remove trailing separator
        if (in_array(mb_substr($inputPath, -1), array('/', '\\'))) {
            $inputPath = mb_substr($inputPath, 0, -1);
        }
        $this->inputPath = $inputPath;

        $basePath = '%BEHAT_WORK_PATH%'.DIRECTORY_SEPARATOR.'features';
        if (null !== $inputPath) {
            $matches = array();
            if (preg_match('/^(.*)\:(\d+)(-(\d+|\*))?$/', $inputPath, $matches)) {
                $inputPath = $matches[1];
            }

            $basePath = $inputPath;
            if (is_file($inputPath)) {
                $basePath = dirname(realpath($inputPath));
            } elseif (is_dir($inputPath.DIRECTORY_SEPARATOR.'features')) {
                $basePath = realpath($inputPath).DIRECTORY_SEPARATOR.'features';
            } elseif (file_exists($inputPath)) {
                $basePath = realpath($inputPath);
            }
        } elseif (!is_dir($basePath) && 'features' === basename($this->workPath)) {
            $basePath = $this->workPath;
        }
        $basePath = $this->preparePath($basePath);

        return $this->pathTokens['BEHAT_BASE_PATH'] = $basePath;
    }

    /**
     * Locates features paths.
     *
     * @return  array
     */
    public function locateFeaturesPaths()
    {
        $inputPath      = $this->inputPath;
        $featuresPath   = $this->container->getParameter('behat.paths.features');
        $lineFilter     = '';

        if (null !== $inputPath) {
            $matches = array();
            if (preg_match('/^(.*)\:(\d+(-(\d+|\*))?)$/', $inputPath, $matches)) {
                $featuresPath   = $matches[1];
                $lineFilter     = ':' . $matches[2];

                if (!is_file($featuresPath) || !is_readable($featuresPath)) {
                    throw new \RuntimeException("File \"$featuresPath\" not exist or is not readable");
                }
            } else {
                $featuresPath = realpath($inputPath);
            }
        }

        $featuresPath   = $this->preparePath($featuresPath);
        $features       = array();
        if (is_dir($featuresPath)) {
            $finder = new Finder();
            foreach ($finder->files()->name('*.feature')->sortByName()->in($featuresPath) as $feature) {
                $features[] = (string) $feature;
            }
        } elseif (is_file($featuresPath)) {
            $features[] = $featuresPath.$lineFilter;
        }

        return $features;
    }

    /**
     * Locates bootstrap files paths.
     *
     * @return  array
     */
    public function locateBootstrapFilesPaths()
    {
        $bootstrapPath = $this->getBootstrapPath();

        $files = array();
        if (is_dir($this->getBootstrapPath())) {
            $finder = new Finder();
            foreach ($finder->files()->name('*.php')->sortByName()->in($bootstrapPath) as $file) {
                $files[] = (string) $file;
            }
        }

        return $files;
    }

    /**
     * Prepare path to find/load methods.
     *
     * Fix directory separators, replace path tokens with configured ones,
     * prepend single filenames with CWD path.
     *
     * @param   string  $path
     *
     * @return  string
     *
     * @uses    pathTokens
     */
    private function preparePath($path)
    {
        $pathTokens = $this->pathTokens;

        $path = preg_replace_callback('/%([^%]+)%/', function($matches) use($pathTokens) {
            $name = $matches[1];
            if (defined($name)) {
                return constant($name);
            } elseif (isset($pathTokens[$name])) {
                return $pathTokens[$name];
            }
            return $matches[0];
        }, str_replace('%%', '%', $path));

        $path = str_replace('/', DIRECTORY_SEPARATOR, str_replace('\\', '/', $path));

        if (!file_exists($path) && file_exists($this->workPath.DIRECTORY_SEPARATOR.$path)) {
            $path = $this->workPath.DIRECTORY_SEPARATOR.$path;
        }
        if (!file_exists($path)) {
            foreach (explode(':', get_include_path()) as $libPath) {
                if (file_exists($libPath.DIRECTORY_SEPARATOR.$path)) {
                    $path = $libPath.DIRECTORY_SEPARATOR.$path;
                    break;
                }
            }
        }

        return realpath($path) ?: $path;
    }
}

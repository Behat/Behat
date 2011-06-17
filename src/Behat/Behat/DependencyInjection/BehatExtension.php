<?php

namespace Behat\Behat\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface,
    Symfony\Component\DependencyInjection\Loader\XmlFileLoader,
    Symfony\Component\Yaml\Yaml,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\Config\Definition\Processor,
    Symfony\Component\Config\FileLocator;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat service container extension.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BehatExtension implements ExtensionInterface
{
    /**
     * Configuration processor.
     *
     * @var     Symfony\Component\Config\Definition\Processor
     */
    private $processor;
    /**
     * Configuration holder.
     *
     * @var     Behat\Behat\DependencyInjection\Configuration
     */
    private $configuration;

    /**
     * Initializes configuration.
     */
    public function __construct()
    {
        $this->processor        = new Processor();
        $this->configuration    = new Configuration();
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->loadDefaults($container);

        // normalize and merge the actual configuration
        $tree   = $this->configuration->getConfigTree();
        $config = $this->processor->process($tree, $configs);

        // load configs DIC
        foreach ($config as $ns => $subconfig) {
            foreach ($subconfig as $key => $value) {
                if ('filters' === $ns) {
                    $parameterName = "gherkin.$ns.$key";
                } else {
                    $parameterName = "behat.$ns.$key";
                }
                $container->setParameter($parameterName, $value);
            }
        }
    }

    /**
     * Loads configuration from specified config file.
     *
     * @param   string                                                      $configFile config file path
     * @param   string                                                      $profile    profile name
     * @param   Symfony\Component\DependencyInjection\ContainerBuilder      $container  service container
     */
    public function loadFromFile($configFile, $profile = 'default', ContainerBuilder $container)
    {
        $config = $this->loadConfigurationFile($configFile, $profile);

        // find path identifiers, started with "!" and remove all related to them paths from config
        if (isset($config['paths'])) {
            foreach ($config['paths'] as $ns => $paths) {
                $pathsToRemove = array();
                foreach ((array) $paths as $num => $path) {
                    if ('!' === $path[0]) {
                        $pathsToRemove[] = ltrim($path, '!');
                        unset($config['paths'][$ns][$num]);
                    }
                }
                foreach ($pathsToRemove as $pathToRemove) {
                    if (false !== $pos = array_search($pathToRemove, $config['paths'][$ns])) {
                        unset($config['paths'][$ns][$pos]);
                    }
                }
            }
        }

        $this->load(array($config), $container);
    }

    /**
     * {@inheritdoc}
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__ . '/config/schema';
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return 'http://behat.com/schema/dic/behat';
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'behat';
    }

    /**
     * Loads information from YAML configuration file.
     *
     * @param   string  $configFile     path to the config file
     * @param   string  $profile        name of the config profile to use
     *
     * @return  array
     */
    protected function loadConfigurationFile($configFile, $profile = 'default')
    {
        if (!is_file($configFile) || !is_readable($configFile)) {
            throw new \InvalidArgumentException("Config file $configFile not found");
        }

        $config = Yaml::parse($configFile);

        $resultConfig = isset($config['default']) ? $config['default'] : array();
        if ('default' !== $profile && isset($config[$profile])) {
            $resultConfig = $this->configMergeRecursiveWithOverwrites($resultConfig, $config[$profile]);
        }

        if (isset($config['imports']) && is_array($config['imports'])) {
            foreach ($config['imports'] as $path) {
                if (!file_exists($path) && file_exists(getcwd().DIRECTORY_SEPARATOR.$path)) {
                    $path = getcwd().DIRECTORY_SEPARATOR.$path;
                }
                if (!file_exists($path)) {
                    foreach (explode(':', get_include_path()) as $libPath) {
                        if (file_exists($libPath.DIRECTORY_SEPARATOR.$path)) {
                            $path = $libPath.DIRECTORY_SEPARATOR.$path;
                            break;
                        }
                    }
                }

                $resultConfig = $this->configMergeRecursiveWithOverwrites(
                    $resultConfig, $this->loadConfigurationFile($path, $profile)
                );
            }
        }

        return $resultConfig;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadDefaults($container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/config'));
        $loader->load('behat.xml');

        $behatClassLoaderReflection = new \ReflectionClass('Behat\Behat\Console\BehatApplication');
        $gherkinParserReflection    = new \ReflectionClass('Behat\Gherkin\Parser');

        $behatLibPath   = realpath(dirname($behatClassLoaderReflection->getFilename()) . '/../../../../');
        $gherkinLibPath = realpath(dirname($gherkinParserReflection->getFilename()) . '/../../../');

        $container->setParameter('gherkin.paths.lib', $gherkinLibPath);
        $container->setParameter('behat.paths.lib', $behatLibPath);
    }

    /**
     * Merges two arrays into one with overwrite. It works the same as array_merge_recursive, but
     * overwrites non-array values instead of turning them into arrays.
     *
     * @param   array  $array1  to
     * @param   array  $array2  from
     *
     * @return  array
     */
    private function configMergeRecursiveWithOverwrites($array1, $array2)
    {
        foreach($array2 as $key => $val) {
            if (array_key_exists($key, $array1) && is_array($val)) {
                $array1[$key] = $this->configMergeRecursiveWithOverwrites($array1[$key], $val);
            } elseif (is_numeric($key)) {
                $array1[] = $val;
            } else {
                $array1[$key] = $val;
            }
        }

        return $array1;
    }
}

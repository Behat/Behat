<?php

namespace Behat\Behat\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface,
    Symfony\Component\DependencyInjection\Loader\XmlFileLoader,
    Symfony\Component\DependencyInjection\DefinitionDecorator,
    Symfony\Component\DependencyInjection\Definition,
    Symfony\Component\DependencyInjection\Reference,
    Symfony\Component\DependencyInjection\Alias,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\Yaml\Yaml,
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
    protected $processor;
    /**
     * Configuration holder.
     *
     * @var     Behat\Behat\DependencyInjection\Configuration
     */
    protected $configuration;

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

        // set internal encoding to UTF-8
        if ('UTF-8' !== mb_internal_encoding()) {
            mb_internal_encoding('UTF-8');
        }

        // normalize and merge the actual configuration
        $tree   = $this->configuration->getConfigTree();
        $config = $this->processor->process($tree, $configs);

        // load configs DIC
        foreach ($config as $ns => $subconfig) {
            foreach ($subconfig as $key => $value) {
                if (is_integer($key)) {
                    $parameterName = "behat.$ns";
                } elseif ('filters' === $ns) {
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
    public function readConfigurationFile($configFile, $profile = 'default', ContainerBuilder $container)
    {
        $config = $this->loadConfigurationFile($configFile, $profile, $container);

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

        return $config;
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
     * @param   string                                                  $configFile
     * @param   string                                                  $profile
     * @param   Symfony\Component\DependencyInjection\ContainerBuilder  $container
     *
     * @return  array
     */
    protected function loadConfigurationFile($configFile, $profile, ContainerBuilder $container)
    {
        if (!is_file($configFile) || !is_readable($configFile)) {
            throw new \InvalidArgumentException("Config file \"$configFile\" not found");
        }

        $config = Yaml::parse($configFile);

        $resultConfig = isset($config['default']) ? $config['default'] : array();
        if ('default' !== $profile && isset($config[$profile])) {
            $resultConfig = $this->configMergeRecursiveWithOverwrites($resultConfig, $config[$profile]);
        }

        if (isset($config['services']) && is_array($config['services'])) {
            foreach ($config['services'] as $id => $service) {
                $this->parseDefinition($id, $service, $configFile, $container);
            }
        }

        if (isset($config['imports']) && is_array($config['imports'])) {
            foreach ($config['imports'] as $path) {
                $resultConfig = $this->configMergeRecursiveWithOverwrites(
                    $resultConfig, $this->parseImport($path, $profile, $container)
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

        $behatLibPath   = dirname($behatClassLoaderReflection->getFilename()) . '/../../../../';
        $gherkinLibPath = dirname($gherkinParserReflection->getFilename()) . '/../../../';

        $container->setParameter('gherkin.paths.lib', $gherkinLibPath);
        $container->setParameter('behat.paths.lib', $behatLibPath);
    }

    /**
     * Parses a definition.
     *
     * @param   string  $id
     * @param   array   $service
     * @param   string  $file
     */
    private function parseDefinition($id, $service, $file, ContainerBuilder $container)
    {
        if (is_string($service) && 0 === strpos($service, '@')) {
            $container->setAlias($id, substr($service, 1));

            return;
        } else if (isset($service['alias'])) {
            $public = !array_key_exists('public', $service) || (Boolean) $service['public'];
            $container->setAlias($id, new Alias($service['alias'], $public));

            return;
        }

        if (isset($service['parent'])) {
            $definition = new DefinitionDecorator($service['parent']);
        } else {
            $definition = new Definition();
        }

        if (isset($service['class'])) {
            $definition->setClass($service['class']);
        }

        if (isset($service['scope'])) {
            $definition->setScope($service['scope']);
        }

        if (isset($service['synthetic'])) {
            $definition->setSynthetic($service['synthetic']);
        }

        if (isset($service['public'])) {
            $definition->setPublic($service['public']);
        }

        if (isset($service['abstract'])) {
            $definition->setAbstract($service['abstract']);
        }

        if (isset($service['factory_class'])) {
            $definition->setFactoryClass($service['factory_class']);
        }

        if (isset($service['factory_method'])) {
            $definition->setFactoryMethod($service['factory_method']);
        }

        if (isset($service['factory_service'])) {
            $definition->setFactoryService($service['factory_service']);
        }

        if (isset($service['file'])) {
            $definition->setFile($service['file']);
        }

        if (isset($service['arguments'])) {
            $definition->setArguments($this->resolveServices($service['arguments']));
        }

        if (isset($service['properties'])) {
            $definition->setProperties($this->resolveServices($service['properties']));
        }

        if (isset($service['configurator'])) {
            if (is_string($service['configurator'])) {
                $definition->setConfigurator($service['configurator']);
            } else {
                $definition->setConfigurator(array($this->resolveServices($service['configurator'][0]), $service['configurator'][1]));
            }
        }

        if (isset($service['calls'])) {
            foreach ($service['calls'] as $call) {
                $definition->addMethodCall($call[0], $this->resolveServices($call[1]));
            }
        }

        if (isset($service['tags'])) {
            if (!is_array($service['tags'])) {
                throw new InvalidArgumentException(sprintf('Parameter "tags" must be an array for service "%s" in %s.', $id, $file));
            }

            foreach ($service['tags'] as $tag) {
                if (!isset($tag['name'])) {
                    throw new InvalidArgumentException(sprintf('A "tags" entry is missing a "name" key for service "%s" in %s.', $id, $file));
                }

                $name = $tag['name'];
                unset($tag['name']);

                $definition->addTag($name, $tag);
            }
        }

        $container->setDefinition($id, $definition);
    }

    /**
     * Parses imports section.
     *
     * @param   string  $path
     * @param   string  $profile
     * @param   Symfony\Component\DependencyInjection\ContainerBuilder  $container
     *
     * @return  array
     */
    private function parseImport($path, $profile, ContainerBuilder $container)
    {
        if (!file_exists($path) && file_exists(getcwd().DIRECTORY_SEPARATOR.$path)) {
            $path = getcwd().DIRECTORY_SEPARATOR.$path;
        }

        if (!file_exists($path)) {
            foreach (explode(PATH_SEPARATOR, get_include_path()) as $libPath) {
                if (file_exists($libPath.DIRECTORY_SEPARATOR.$path)) {
                    $path = $libPath.DIRECTORY_SEPARATOR.$path;
                    break;
                }
            }
        }

        return $this->loadConfigurationFile($path, $profile, $container);
    }

    /**
     * Resolves services.
     *
     * @param string $value
     *
     * @return Reference
     */
    private function resolveServices($value)
    {
        if (is_array($value)) {
            $value = array_map(array($this, 'resolveServices'), $value);
        } else if (is_string($value) &&  0 === strpos($value, '@')) {
            if (0 === strpos($value, '@?')) {
                $value = substr($value, 2);
                $invalidBehavior = ContainerInterface::IGNORE_ON_INVALID_REFERENCE;
            } else {
                $value = substr($value, 1);
                $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
            }

            if ('=' === substr($value, -1)) {
                $value = substr($value, 0, -1);
                $strict = false;
            } else {
                $strict = true;
            }

            $value = new Reference($value, $invalidBehavior, $strict);
        }

        return $value;
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

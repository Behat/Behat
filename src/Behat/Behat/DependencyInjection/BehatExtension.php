<?php

namespace Behat\Behat\DependencyInjection;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Extension\ExtensionManager;
use ReflectionClass;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Behat service container extension.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BehatExtension
{
    /**
     * @var string
     */
    protected $basePath;
    /**
     * @var Processor
     */
    protected $processor;
    /**
     * @var Configuration\Configuration
     */
    protected $configuration;
    /**
     * @var ExtensionManager
     */
    protected $extensionManager;

    /**
     * Initializes configuration.
     *
     * @param string $basePath
     */
    public function __construct($basePath)
    {
        $this->basePath = $basePath;
        $this->processor = new Processor();
        $this->configuration = new Configuration\Configuration();
        $this->extensionManager = new ExtensionManager($basePath);
    }

    /**
     * Loads container configuration.
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->registerDefaults($container);

        // activateExtension and normalize specified by user extensions
        foreach ($configs as $i => $config) {
            if (isset($config['extensions'])) {
                $extensions = array();
                foreach ($config['extensions'] as $extensionLocator => $extensionConfig) {
                    $extension = $this->extensionManager->activateExtension($extensionLocator);
                    $extensions[$extension->getName()] = $extensionConfig;
                }
                $configs[$i]['extensions'] = $extensions;
            }
        }

        // set list of extensions to container
        $container->setParameter('extension.classes', $this->extensionManager->getExtensionClasses());

        // normalize and merge the actual configuration
        $tree = $this->configuration->getConfigTree($this->extensionManager->getExtensions());
        $config = $this->processor->process($tree, $configs);

        // register configuration sections
        $this->registerAutoloadConfiguration($config['autoload'], $container);
        $this->registerSuitesConfiguration($config['suites'], $container);
        $this->registerFormattersConfiguration($config['formatters'], $container);
        $this->registerOptionsConfiguration($config['options'], $container);
        $this->registerExtensionsConfiguration($config['extensions'], $container);

        $this->addCompilerPasses($container);
    }

    /**
     * Registers Behat default configuration.
     *
     * @param ContainerBuilder $container
     */
    protected function registerDefaults(ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/config'));
        $loader->load('services.xml');

        $behatClassLoaderReflection = new ReflectionClass('Behat\Behat\Console\BehatApplication');
        $gherkinParserReflection = new ReflectionClass('Behat\Gherkin\Gherkin');

        $behatLibPath = dirname($behatClassLoaderReflection->getFilename()) . '/../../../../';
        $gherkinLibPath = dirname($gherkinParserReflection->getFilename()) . '/../../../';

        $container->setParameter('paths.base', rtrim($this->basePath, DIRECTORY_SEPARATOR));
        $container->setParameter('paths.lib', rtrim(realpath($behatLibPath), DIRECTORY_SEPARATOR));
        $container->setParameter('paths.gherkin.lib', rtrim(realpath($gherkinLibPath), DIRECTORY_SEPARATOR));
    }

    /**
     * Registers autoload configuration.
     *
     * @param array            $autoload
     * @param ContainerBuilder $container
     */
    protected function registerAutoloadConfiguration(array $autoload, ContainerBuilder $container)
    {
        $loaderDefinition = $container->getDefinition('class_loader');
        foreach ($autoload as $prefix => $path) {
            $loaderDefinition->addMethodCall('addPrefix', array($prefix, $path));
        }
    }

    /**
     * Registers suites configuration.
     *
     * @param array            $suites
     * @param ContainerBuilder $container
     */
    protected function registerSuitesConfiguration(array $suites, ContainerBuilder $container)
    {
        $loaderDefinition = $container->getDefinition('suite.suites_loader');
        foreach ($suites as $name => $parameters) {
            $suiteDefinition = new Definition('Behat\Behat\Suite\SuiteInterface');
            $suiteDefinition->setFactoryService('suite.suite_factory');
            $suiteDefinition->setFactoryMethod('createSuite');
            $suiteDefinition->setArguments(array($name, $parameters['type'], $parameters));

            $loaderDefinition->addMethodCall('registerSuite', array($suiteDefinition));
        }
    }

    /**
     * Registers formatters configuration.
     *
     * @param array            $formatters
     * @param ContainerBuilder $container
     */
    protected function registerFormattersConfiguration(array $formatters, ContainerBuilder $container)
    {
        $managerDefinition = $container->getDefinition('output.formatter_manager');
        foreach ($formatters as $name => $parameters) {
            if ($parameters['enabled']) {
                $managerDefinition->addMethodCall('enableFormatter', array($name));
            } else {
                $managerDefinition->addMethodCall('disableFormatter', array($name));
            }

            unset($parameters['enabled']);
            $managerDefinition->addMethodCall('setFormatterParameters', array($name, $parameters));
        }
    }

    /**
     * Registers additional Behat options.
     *
     * @param array            $options
     * @param ContainerBuilder $container
     */
    protected function registerOptionsConfiguration(array $options, ContainerBuilder $container)
    {
        if ($options['cache_path']) {
            $cacheDefinition = new Definition('Behat\Gherkin\Cache\FileCache', array($options['cache_path']));
            $fileLoaderDefinition = $container->getDefinition('gherkin.loader.gherkin_file');
            $fileLoaderDefinition->addMethodCall('setCache', array($cacheDefinition));

            $failedScenarios = $container->getDefinition('run_control.cache_failed_scenarios_for_rerun');
            $failedScenarios->addMethodCall('setCache', array($options['cache_path']));
        }

        $container->setParameter('options.strict', $options['strict']);
        $container->setParameter('options.dry_run', $options['dry_run']);
        $container->setParameter('options.stop_on_failure', $options['stop_on_failure']);
        $container->setParameter('options.append_snippets', $options['append_snippets']);
        $container->setParameter('options.error_reporting', $options['error_reporting']);
    }

    /**
     * Registers extensions configuration.
     *
     * @param array            $extensions
     * @param ContainerBuilder $container
     */
    protected function registerExtensionsConfiguration(array $extensions, ContainerBuilder $container)
    {
        foreach ($extensions as $name => $extensionConfig) {
            // load extension from manager
            $extension = $this->extensionManager->getExtension($name);

            // create temporary container
            $tempContainer = new ContainerBuilder(new ParameterBag(array(
                'paths.base'        => $container->getParameter('paths.base'),
                'extension.classes' => $container->getParameter('extension.classes'),
            )));
            $tempContainer->addObjectResource($extension);

            // load extension into temporary container
            $extension->load($extensionConfig, $tempContainer);

            // merge temporary container into normal one
            $container->merge($tempContainer);

            // add extension compiler passes
            array_map(array($container, 'addCompilerPass'), $extension->getCompilerPasses());
        }
    }

    /**
     * Adds core compiler passes to container.
     *
     * @param ContainerBuilder $container
     */
    protected function addCompilerPasses(ContainerBuilder $container)
    {
        $container->addCompilerPass(new Compiler\ConsoleProcessorsPass());
        $container->addCompilerPass(new Compiler\OutputFormattersPass());
        $container->addCompilerPass(new Compiler\SuiteGeneratorsPass());
        $container->addCompilerPass(new Compiler\EventSubscribersPass());
        $container->addCompilerPass(new Compiler\FeaturesLoadersPass());
        $container->addCompilerPass(new Compiler\GherkinLoadersPass());
        $container->addCompilerPass(new Compiler\ContextLoadersPass());
        $container->addCompilerPass(new Compiler\ContextInitializersPass());
    }
}

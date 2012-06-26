<?php

namespace Behat\Behat\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface,
    Symfony\Component\DependencyInjection\Loader\XmlFileLoader,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\ParameterBag\ParameterBag,
    Symfony\Component\Config\Definition\Processor,
    Symfony\Component\Config\FileLocator;

use Behat\Behat\Extension\ExtensionManager;

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
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BehatExtension implements ExtensionInterface
{
    protected $basePath;
    protected $processor;
    protected $configuration;
    protected $extensionManager;

    /**
     * Initializes configuration.
     */
    public function __construct($basePath)
    {
        $this->basePath         = $basePath;
        $this->processor        = new Processor();
        $this->configuration    = new Configuration\Configuration();
        $this->extensionManager = new ExtensionManager($basePath);
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->loadDefaults($container);
        $container->setParameter('behat.paths.base', $this->basePath);

        // set internal encoding to UTF-8
        if ('UTF-8' !== mb_internal_encoding()) {
            mb_internal_encoding('UTF-8');
        }

        // activate and normalize specified by user extensions
        foreach ($configs as $i => $config) {
            if (isset($config['extensions'])) {
                $extensions = array();
                foreach ($config['extensions'] as $id => $extensionConfig) {
                    $activationId = $this->extensionManager->activateExtension($id);
                    $extensions[$activationId] = $extensionConfig;
                }
                $configs[$i]['extensions'] = $extensions;
            }
        }

        // set list of extensions to container
        $container->setParameter('behat.extension.classes',
            $this->extensionManager->getExtensionClasses()
        );

        // normalize and merge the actual configuration
        $tree   = $this->configuration->getConfigTree($this->extensionManager);
        $config = $this->processor->process($tree, $configs);

        if (isset($config['paths'])) {
            $this->loadPathsConfiguration($config['paths'], $container);
        }
        if (isset($config['context'])) {
            $this->loadContextConfiguration($config['context'], $container);
        }
        if (isset($config['formatter'])) {
            $this->loadFormatterConfiguration($config['formatter'], $container);
        }
        if (isset($config['options'])) {
            $this->loadOptionsConfiguration($config['options'], $container);
        }
        if (isset($config['filters'])) {
            $this->loadFiltersConfiguration($config['filters'], $container);
        }
        if (isset($config['extensions'])) {
            $this->loadExtensionsConfiguration($config['extensions'], $container);
        }

        $this->resolveRelativePaths($container);
        $this->addCompilerPasses($container);
    }

    /**
     * Loads paths configuration.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    protected function loadPathsConfiguration(array $config, ContainerBuilder $container)
    {
        foreach ($config as $key => $path) {
            $container->setParameter('behat.paths.'.$key, $path);
        }
    }

    /**
     * Loads context configuration.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    protected function loadContextConfiguration(array $config, ContainerBuilder $container)
    {
        if ('FeatureContext' !== $config['class']) {
            $container->setParameter('behat.context.class.force', true);
        }

        foreach ($config as $key => $value) {
            $container->setParameter('behat.context.'.$key, $value);
        }
    }

    /**
     * Loads formatter(s) configuration.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    protected function loadFormatterConfiguration(array $config, ContainerBuilder $container)
    {
        foreach ($config as $key => $value) {
            $container->setParameter('behat.formatter.'.$key, $value);
        }
    }

    /**
     * Loads behat options configuration.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    protected function loadOptionsConfiguration(array $config, ContainerBuilder $container)
    {
        foreach ($config as $key => $value) {
            $container->setParameter('behat.options.'.$key, $value);
        }
    }

    /**
     * Loads gherkin filters configuration.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    protected function loadFiltersConfiguration(array $config, ContainerBuilder $container)
    {
        foreach ($config as $key => $filter) {
            $container->setParameter('gherkin.filters.'.$key, $filter);
        }
    }

    /**
     * Loads extensions configuration.
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    protected function loadExtensionsConfiguration(array $config, ContainerBuilder $container)
    {
        foreach ($config as $id => $extensionConfig) {
            // load extension from manager
            $extension = $this->extensionManager->getExtension($id);

            // create temporary container
            $tempContainer = new ContainerBuilder(new ParameterBag(array(
                'behat.paths.base'        => $container->getParameter('behat.paths.base'),
                'behat.extension.classes' => $container->getParameter('behat.extension.classes'),
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
     * Resolves relative behat.paths.* parameters in container.
     *
     * @param ContainerBuilder $container
     */
    protected function resolveRelativePaths(ContainerBuilder $container)
    {
        $featuresPath  = $container->getParameter('behat.paths.features');
        $bootstrapPath = $container->getParameter('behat.paths.bootstrap');
        $parameterBag  = $container->getParameterBag();
        $featuresPath  = $parameterBag->resolveValue($featuresPath);
        $bootstrapPath = $parameterBag->resolveValue($bootstrapPath);

        if (!$this->isAbsolutePath($featuresPath)) {
            $featuresPath = $this->basePath.DIRECTORY_SEPARATOR.$featuresPath;
            $container->setParameter('behat.paths.features', $featuresPath);
        }
        if (!$this->isAbsolutePath($bootstrapPath)) {
            $bootstrapPath = $this->basePath.DIRECTORY_SEPARATOR.$bootstrapPath;
            $container->setParameter('behat.paths.bootstrap', $bootstrapPath);
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
        $container->addCompilerPass(new Compiler\GherkinLoadersPass());
        $container->addCompilerPass(new Compiler\ContextLoadersPass());
        $container->addCompilerPass(new Compiler\ContextClassGuessersPass());
        $container->addCompilerPass(new Compiler\ContextInitializersPass());
        $container->addCompilerPass(new Compiler\DefinitionProposalsPass());
        $container->addCompilerPass(new Compiler\FormattersPass());
        $container->addCompilerPass(new Compiler\EventSubscribersPass());
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
     * Returns whether the file path is an absolute path.
     *
     * @param string $file A file path
     *
     * @return Boolean
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

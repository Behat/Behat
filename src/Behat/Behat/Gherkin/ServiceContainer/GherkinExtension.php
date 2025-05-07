<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Gherkin\ServiceContainer;

use Behat\Gherkin\Keywords\CachedArrayKeywords;
use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\Filesystem\ServiceContainer\FilesystemExtension;
use Behat\Testwork\ServiceContainer\Exception\ExtensionException;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Behat\Testwork\Specification\ServiceContainer\SpecificationExtension;
use Behat\Testwork\Suite\ServiceContainer\SuiteExtension;
use Behat\Testwork\Translator\ServiceContainer\TranslatorExtension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Extends Behat with gherkin suites and features.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class GherkinExtension implements Extension
{
    /*
     * Available services
     */
    public const MANAGER_ID = 'gherkin';
    public const KEYWORDS_DUMPER_ID = 'gherkin.keywords_dumper';
    public const KEYWORDS_ID = 'gherkin.keywords';

    /*
     * Available extension points
     */
    public const LOADER_TAG = 'gherkin.loader';

    /**
     * @var ServiceProcessor
     */
    private $processor;

    /**
     * Initializes extension.
     */
    public function __construct(?ServiceProcessor $processor = null)
    {
        $this->processor = $processor ?: new ServiceProcessor();
    }

    public function getConfigKey()
    {
        return 'gherkin';
    }

    public function initialize(ExtensionManager $extensionManager)
    {
    }

    public function configure(ArrayNodeDefinition $builder)
    {
        $childrenBuilder = $builder
            ->addDefaultsIfNotSet()
            ->children()
        ;
        $childrenBuilder
            ->scalarNode('cache')
                ->info('Sets the gherkin parser cache folder')
                ->defaultValue(
                    is_writable(sys_get_temp_dir())
                        ? sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat_gherkin_cache'
                        : null
                )
        ;
        $childrenBuilder
            ->arrayNode('filters')
                ->info('Sets the gherkin filters (overridable by CLI options)')
                ->performNoDeepMerging()
                ->defaultValue([])
                ->useAttributeAsKey('name')
                ->prototype('scalar')->end()
        ;
    }

    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadParameters($container);
        $this->loadGherkin($container);
        $this->loadKeywords($container);
        $this->loadParser($container);
        $this->loadDefaultLoaders($container, $config['cache']);
        $this->loadProfileFilters($container, $config['filters']);
        $this->loadSyntaxController($container);
        $this->loadFilterController($container);
        $this->loadSuiteWithPathsSetup($container);
        $this->loadFilesystemFeatureLocator($container);
        $this->loadFilesystemScenariosListLocator($container);
        $this->loadFilesystemRerunScenariosListLocator($container);
    }

    public function process(ContainerBuilder $container)
    {
        $this->processLoaders($container);
    }

    /**
     * Loads default container parameters.
     */
    private function loadParameters(ContainerBuilder $container)
    {
        $container->setParameter(
            'suite.generic.default_settings',
            [
                'paths' => ['%paths.base%/features'],
                'contexts' => ['FeatureContext'],
            ]
        );
    }

    /**
     * Loads gherkin service.
     */
    private function loadGherkin(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Gherkin\Gherkin');
        $container->setDefinition(self::MANAGER_ID, $definition);
    }

    /**
     * Loads keyword services.
     */
    private function loadKeywords(ContainerBuilder $container)
    {
        $definition = new Definition(CachedArrayKeywords::class);
        $definition->setFactory([CachedArrayKeywords::class, 'withDefaultKeywords']);
        $container->setDefinition(self::KEYWORDS_ID, $definition);

        $definition = new Definition('Behat\Gherkin\Keywords\KeywordsDumper', [
            new Reference(self::KEYWORDS_ID),
        ]);
        $container->setDefinition(self::KEYWORDS_DUMPER_ID, $definition);
    }

    /**
     * Loads gherkin parser.
     */
    private function loadParser(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Gherkin\Parser', [
            new Reference('gherkin.lexer'),
        ]);
        $container->setDefinition('gherkin.parser', $definition);

        $definition = new Definition('Behat\Gherkin\Lexer', [
            new Reference('gherkin.keywords'),
        ]);
        $container->setDefinition('gherkin.lexer', $definition);
    }

    /**
     * Loads gherkin loaders.
     *
     * @param string           $cachePath
     */
    private function loadDefaultLoaders(ContainerBuilder $container, $cachePath)
    {
        $definition = new Definition('Behat\Gherkin\Loader\GherkinFileLoader', [
            new Reference('gherkin.parser'),
        ]);

        if ($cachePath) {
            $cacheDefinition = new Definition('Behat\Gherkin\Cache\FileCache', [$cachePath]);
        } else {
            $cacheDefinition = new Definition('Behat\Gherkin\Cache\MemoryCache');
        }

        $definition->addMethodCall('setCache', [$cacheDefinition]);
        $definition->addMethodCall('setBasePath', ['%paths.base%']);
        $definition->addTag(self::LOADER_TAG, ['priority' => 50]);
        $container->setDefinition('gherkin.loader.gherkin_file', $definition);
    }

    /**
     * Loads profile-level gherkin filters.
     */
    private function loadProfileFilters(ContainerBuilder $container, array $filters)
    {
        $gherkin = $container->getDefinition(self::MANAGER_ID);
        foreach ($filters as $type => $filterString) {
            $filter = $this->createFilterDefinition($type, $filterString);
            $gherkin->addMethodCall('addFilter', [$filter]);
        }
    }

    /**
     * Loads syntax controller.
     */
    private function loadSyntaxController(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Gherkin\Cli\SyntaxController', [
            new Reference(self::KEYWORDS_DUMPER_ID),
            new Reference(TranslatorExtension::TRANSLATOR_ID),
        ]);
        $definition->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 600]);
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.gherkin_syntax', $definition);
    }

    /**
     * Loads filter controller.
     */
    private function loadFilterController(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Gherkin\Cli\FilterController', [
            new Reference(self::MANAGER_ID),
        ]);
        $definition->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 700]);
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.gherkin_filters', $definition);
    }

    /**
     * Loads suite with paths setup.
     */
    private function loadSuiteWithPathsSetup(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Gherkin\Suite\Setup\SuiteWithPathsSetup', [
            '%paths.base%',
            new Reference(FilesystemExtension::LOGGER_ID),
        ]);
        $definition->addTag(SuiteExtension::SETUP_TAG, ['priority' => 50]);
        $container->setDefinition(SuiteExtension::SETUP_TAG . '.suite_with_paths', $definition);
    }

    /**
     * Loads filesystem feature locator.
     */
    private function loadFilesystemFeatureLocator(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Gherkin\Specification\Locator\FilesystemFeatureLocator', [
            new Reference(self::MANAGER_ID),
            '%paths.base%',
        ]);
        $definition->addTag(SpecificationExtension::LOCATOR_TAG, ['priority' => 60]);
        $container->setDefinition(SpecificationExtension::LOCATOR_TAG . '.filesystem_feature', $definition);
    }

    /**
     * Loads filesystem scenarios list locator.
     */
    private function loadFilesystemScenariosListLocator(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Gherkin\Specification\Locator\FilesystemScenariosListLocator', [
            new Reference(self::MANAGER_ID),
        ]);
        $definition->addTag(SpecificationExtension::LOCATOR_TAG, ['priority' => 50]);
        $container->setDefinition(SpecificationExtension::LOCATOR_TAG . '.filesystem_scenarios_list', $definition);
    }

    /**
     * Loads filesystem rerun scenarios list locator.
     */
    private function loadFilesystemRerunScenariosListLocator(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Gherkin\Specification\Locator\FilesystemRerunScenariosListLocator', [
            new Reference(self::MANAGER_ID),
        ]);
        $definition->addTag(SpecificationExtension::LOCATOR_TAG, ['priority' => 50]);
        $container->setDefinition(SpecificationExtension::LOCATOR_TAG . '.filesystem_rerun_scenarios_list', $definition);
    }

    /**
     * Processes all available gherkin loaders.
     */
    private function processLoaders(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::LOADER_TAG);
        $definition = $container->getDefinition(self::MANAGER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('addLoader', [$reference]);
        }
    }

    /**
     * Creates filter definition of provided type.
     *
     * @param string $type
     * @param string $filterString
     *
     * @return Definition
     *
     * @throws ExtensionException If filter type is not recognised
     */
    private function createFilterDefinition($type, $filterString)
    {
        if ('role' === $type) {
            return new Definition('Behat\Gherkin\Filter\RoleFilter', [$filterString]);
        }

        if ('name' === $type) {
            return new Definition('Behat\Gherkin\Filter\NameFilter', [$filterString]);
        }

        if ('tags' === $type) {
            return new Definition('Behat\Gherkin\Filter\TagFilter', [$filterString]);
        }

        if ('narrative' === $type) {
            return new Definition('Behat\Gherkin\Filter\NarrativeFilter', [$filterString]);
        }

        throw new ExtensionException(sprintf(
            '`%s` filter is not supported by the `filters` option of gherkin extension. Supported types are `%s`.',
            $type,
            implode('`, `', ['narrative', 'role', 'name', 'tags'])
        ), 'gherkin');
    }
}

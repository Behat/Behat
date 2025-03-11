<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\Argument\ServiceContainer\ArgumentExtension;
use Behat\Behat\Gherkin\ServiceContainer\GherkinExtension;
use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\Environment\ServiceContainer\EnvironmentExtension;
use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Behat\Testwork\Suite\ServiceContainer\SuiteExtension;
use Behat\Testwork\Translator\ServiceContainer\TranslatorExtension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Extends Behat with definition services.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class DefinitionExtension implements Extension
{
    /*
     * Available services
     */
    public const FINDER_ID = 'definition.finder';
    public const REPOSITORY_ID = 'definition.repository';
    public const PATTERN_TRANSFORMER_ID = 'definition.pattern_transformer';
    public const WRITER_ID = 'definition.writer';
    public const DEFINITION_TRANSLATOR_ID = 'definition.translator';

    /*
     * Available extension points
     */
    public const SEARCH_ENGINE_TAG = 'definition.search_engine';
    public const PATTERN_POLICY_TAG = 'definition.pattern_policy';
    public const DOC_BLOCK_HELPER_ID = 'definition.doc_block_helper';

    /**
     * @var ServiceProcessor
     */
    private $processor;

    /**
     * Initializes compiler pass.
     *
     * @param null|ServiceProcessor $processor
     */
    public function __construct(?ServiceProcessor $processor = null)
    {
        $this->processor = $processor ?: new ServiceProcessor();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return 'definitions';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('print_unused_definitions')
            ->defaultFalse()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadFinder($container);
        $this->loadRepository($container);
        $this->loadWriter($container);
        $this->loadPatternTransformer($container);
        $this->loadDefinitionTranslator($container);
        $this->loadDefaultSearchEngines($container);
        $this->loadDefaultPatternPolicies($container);
        $this->loadAnnotationReader($container);
        $this->loadAttributeReader($container);
        $this->loadDefinitionPrinters($container);
        $this->loadControllers($container, $config['print_unused_definitions']);
        $this->loadDocblockHelper($container);
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->processSearchEngines($container);
        $this->processPatternPolicies($container);
    }

    /**
     * Loads definition finder.
     *
     * @param ContainerBuilder $container
     */
    private function loadFinder(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Definition\DefinitionFinder');
        $container->setDefinition(self::FINDER_ID, $definition);
    }

    /**
     * Loads definition repository.
     *
     * @param ContainerBuilder $container
     */
    private function loadRepository(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Definition\DefinitionRepository', [
            new Reference(EnvironmentExtension::MANAGER_ID),
        ]);
        $container->setDefinition(self::REPOSITORY_ID, $definition);
    }

    /**
     * Loads definition writer.
     *
     * @param ContainerBuilder $container
     */
    private function loadWriter(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Definition\DefinitionWriter', [
            new Reference(EnvironmentExtension::MANAGER_ID),
            new Reference(self::REPOSITORY_ID),
        ]);
        $container->setDefinition(self::WRITER_ID, $definition);
    }

    /**
     * Loads definition pattern transformer.
     *
     * @param ContainerBuilder $container
     */
    private function loadPatternTransformer(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Definition\Pattern\PatternTransformer');
        $container->setDefinition(self::PATTERN_TRANSFORMER_ID, $definition);
    }

    /**
     * Loads definition translator.
     *
     * @param ContainerBuilder $container
     */
    private function loadDefinitionTranslator(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Definition\Translator\DefinitionTranslator', [
            new Reference(TranslatorExtension::TRANSLATOR_ID),
        ]);
        $container->setDefinition(self::DEFINITION_TRANSLATOR_ID, $definition);
    }

    /**
     * Loads default search engines.
     *
     * @param ContainerBuilder $container
     */
    private function loadDefaultSearchEngines(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Definition\Search\RepositorySearchEngine', [
            new Reference(self::REPOSITORY_ID),
            new Reference(self::PATTERN_TRANSFORMER_ID),
            new Reference(self::DEFINITION_TRANSLATOR_ID),
            new Reference(ArgumentExtension::PREG_MATCH_ARGUMENT_ORGANISER_ID),
        ]);
        $definition->addTag(self::SEARCH_ENGINE_TAG, ['priority' => 50]);
        $container->setDefinition(self::SEARCH_ENGINE_TAG . '.repository', $definition);
    }

    /**
     * Loads default pattern policies.
     *
     * @param ContainerBuilder $container
     */
    private function loadDefaultPatternPolicies(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Definition\Pattern\Policy\TurnipPatternPolicy');
        $definition->addTag(self::PATTERN_POLICY_TAG, ['priority' => 50]);
        $container->setDefinition(self::PATTERN_POLICY_TAG . '.turnip', $definition);

        $definition = new Definition('Behat\Behat\Definition\Pattern\Policy\RegexPatternPolicy');
        $definition->addTag(self::PATTERN_POLICY_TAG, ['priority' => 60]);
        $container->setDefinition(self::PATTERN_POLICY_TAG . '.regex', $definition);
    }

    /**
     * Loads definition annotation reader.
     *
     * @param ContainerBuilder $container
     */
    private function loadAnnotationReader(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Definition\Context\Annotation\DefinitionAnnotationReader');
        $definition->addTag(ContextExtension::ANNOTATION_READER_TAG, ['priority' => 50]);
        $container->setDefinition(ContextExtension::ANNOTATION_READER_TAG . '.definition', $definition);
    }

    /**
     * Loads definition Attribute reader.
     *
     * @param ContainerBuilder $container
     */
    private function loadAttributeReader(ContainerBuilder $container)
    {
        $definition = new Definition('\Behat\Behat\Definition\Context\Attribute\DefinitionAttributeReader', [
            new Reference(self::DOC_BLOCK_HELPER_ID),
        ]);
        $definition->addTag(ContextExtension::ATTRIBUTE_READER_TAG, ['priority' => 50]);
        $container->setDefinition(ContextExtension::ATTRIBUTE_READER_TAG . '.definition', $definition);
    }

    /**
     * Loads definition printers.
     *
     * @param ContainerBuilder $container
     */
    private function loadDefinitionPrinters(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Definition\Printer\ConsoleDefinitionInformationPrinter', [
            new Reference(CliExtension::OUTPUT_ID),
            new Reference(self::PATTERN_TRANSFORMER_ID),
            new Reference(self::DEFINITION_TRANSLATOR_ID),
            new Reference(GherkinExtension::KEYWORDS_ID),
        ]);
        $container->setDefinition($this->getInformationPrinterId(), $definition);

        $definition = new Definition('Behat\Behat\Definition\Printer\ConsoleDefinitionListPrinter', [
            new Reference(CliExtension::OUTPUT_ID),
            new Reference(self::PATTERN_TRANSFORMER_ID),
            new Reference(self::DEFINITION_TRANSLATOR_ID),
            new Reference(GherkinExtension::KEYWORDS_ID),
        ]);
        $container->setDefinition($this->getListPrinterId(), $definition);
    }

    private function loadControllers(ContainerBuilder $container, bool $printUnusedDefinitions): void
    {
        $definition = new Definition('Behat\Behat\Definition\Cli\AvailableDefinitionsController', [
            new Reference(SuiteExtension::REGISTRY_ID),
            new Reference(self::WRITER_ID),
            new Reference($this->getListPrinterId()),
            new Reference($this->getInformationPrinterId()),
        ]);
        $definition->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 500]);
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.available_definitions', $definition);

        $definition = new Definition('Behat\Behat\Definition\Cli\UnusedDefinitionsController', [
            new Reference(self::REPOSITORY_ID),
            new Reference(EventDispatcherExtension::DISPATCHER_ID),
            new Reference($this->getInformationPrinterId()),
            $printUnusedDefinitions,
        ]);
        $definition->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 300]);
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.unused_definitions', $definition);
    }

    /**
     * Loads DocBlockHelper
     *
     * @param ContainerBuilder $container
     */
    private function loadDocblockHelper(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Context\Annotation\DocBlockHelper');

        $container->setDefinition(self::DOC_BLOCK_HELPER_ID, $definition);
    }

    /**
     * Processes all search engines in the container.
     *
     * @param ContainerBuilder $container
     */
    private function processSearchEngines(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::SEARCH_ENGINE_TAG);
        $definition = $container->getDefinition(self::FINDER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerSearchEngine', [$reference]);
        }
    }

    /**
     * Processes all pattern policies.
     *
     * @param ContainerBuilder $container
     */
    private function processPatternPolicies(ContainerBuilder $container)
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::PATTERN_POLICY_TAG);
        $definition = $container->getDefinition(self::PATTERN_TRANSFORMER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerPatternPolicy', [$reference]);
        }
    }

    /**
     * returns list printer service id.
     *
     * @return string
     */
    private function getListPrinterId()
    {
        return 'definition.list_printer';
    }

    /**
     * Returns information printer service id.
     *
     * @return string
     */
    private function getInformationPrinterId()
    {
        return 'definition.information_printer';
    }
}

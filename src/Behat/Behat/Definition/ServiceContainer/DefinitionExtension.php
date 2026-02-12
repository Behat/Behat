<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\ServiceContainer;

use Behat\Behat\Context\Annotation\DocBlockHelper;
use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Behat\Definition\Cli\AvailableDefinitionsController;
use Behat\Behat\Definition\Cli\UnusedDefinitionsController;
use Behat\Behat\Definition\Context\Attribute\DefinitionAttributeReader;
use Behat\Behat\Definition\DefinitionFinder;
use Behat\Behat\Definition\DefinitionRepository;
use Behat\Behat\Definition\DefinitionWriter;
use Behat\Behat\Definition\Pattern\PatternTransformer;
use Behat\Behat\Definition\Pattern\Policy\RegexPatternPolicy;
use Behat\Behat\Definition\Pattern\Policy\TurnipPatternPolicy;
use Behat\Behat\Definition\Pattern\SimpleStepMethodNameSuggester;
use Behat\Behat\Definition\Printer\ConsoleDefinitionInformationPrinter;
use Behat\Behat\Definition\Printer\ConsoleDefinitionListPrinter;
use Behat\Behat\Definition\Search\RepositorySearchEngine;
use Behat\Behat\Definition\Translator\DefinitionTranslator;
use Behat\Behat\Gherkin\ServiceContainer\GherkinExtension;
use Behat\Testwork\Argument\ServiceContainer\ArgumentExtension;
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
    public const STEP_METHOD_NAME_SUGGESTER_ID = 'definition.step_method_name_suggester_id';

    /*
     * Available extension points
     */
    public const SEARCH_ENGINE_TAG = 'definition.search_engine';
    public const PATTERN_POLICY_TAG = 'definition.pattern_policy';
    public const DOC_BLOCK_HELPER_ID = 'definition.doc_block_helper';

    private readonly ServiceProcessor $processor;

    /**
     * Initializes compiler pass.
     */
    public function __construct(?ServiceProcessor $processor = null)
    {
        $this->processor = $processor ?: new ServiceProcessor();
    }

    public function getConfigKey(): string
    {
        return 'definitions';
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('print_unused_definitions')
            ->defaultFalse()
        ;
    }

    public function load(ContainerBuilder $container, array $config): void
    {
        $this->loadFinder($container);
        $this->loadRepository($container);
        $this->loadWriter($container);
        $this->loadPatternTransformer($container);
        $this->loadDefinitionTranslator($container);
        $this->loadDefaultSearchEngines($container);
        $this->loadStepMethodNameSuggester($container);
        $this->loadDefaultPatternPolicies($container);
        $this->loadAttributeReader($container);
        $this->loadDefinitionPrinters($container);
        $this->loadControllers($container, $config['print_unused_definitions']);
        $this->loadDocblockHelper($container);
    }

    public function process(ContainerBuilder $container): void
    {
        $this->processSearchEngines($container);
        $this->processPatternPolicies($container);
    }

    /**
     * Loads definition finder.
     */
    private function loadFinder(ContainerBuilder $container): void
    {
        $definition = new Definition(DefinitionFinder::class);
        $container->setDefinition(self::FINDER_ID, $definition);
    }

    /**
     * Loads definition repository.
     */
    private function loadRepository(ContainerBuilder $container): void
    {
        $definition = new Definition(DefinitionRepository::class, [
            new Reference(EnvironmentExtension::MANAGER_ID),
        ]);
        $container->setDefinition(self::REPOSITORY_ID, $definition);
    }

    /**
     * Loads definition writer.
     */
    private function loadWriter(ContainerBuilder $container): void
    {
        $definition = new Definition(DefinitionWriter::class, [
            new Reference(EnvironmentExtension::MANAGER_ID),
            new Reference(self::REPOSITORY_ID),
        ]);
        $container->setDefinition(self::WRITER_ID, $definition);
    }

    /**
     * Loads definition pattern transformer.
     */
    private function loadPatternTransformer(ContainerBuilder $container): void
    {
        $definition = new Definition(PatternTransformer::class);
        $container->setDefinition(self::PATTERN_TRANSFORMER_ID, $definition);
    }

    /**
     * Loads definition translator.
     */
    private function loadDefinitionTranslator(ContainerBuilder $container): void
    {
        $definition = new Definition(DefinitionTranslator::class, [
            new Reference(TranslatorExtension::TRANSLATOR_ID),
        ]);
        $container->setDefinition(self::DEFINITION_TRANSLATOR_ID, $definition);
    }

    /**
     * Loads default search engines.
     */
    private function loadDefaultSearchEngines(ContainerBuilder $container): void
    {
        $definition = new Definition(RepositorySearchEngine::class, [
            new Reference(self::REPOSITORY_ID),
            new Reference(self::PATTERN_TRANSFORMER_ID),
            new Reference(self::DEFINITION_TRANSLATOR_ID),
            new Reference(ArgumentExtension::PREG_MATCH_ARGUMENT_ORGANISER_ID),
        ]);
        $definition->addTag(self::SEARCH_ENGINE_TAG, ['priority' => 50]);
        $container->setDefinition(self::SEARCH_ENGINE_TAG . '.repository', $definition);
    }

    private function loadStepMethodNameSuggester(ContainerBuilder $container): void
    {
        $definition = new Definition(SimpleStepMethodNameSuggester::class);
        $container->setDefinition(self::STEP_METHOD_NAME_SUGGESTER_ID, $definition);
    }

    /**
     * Loads default pattern policies.
     */
    private function loadDefaultPatternPolicies(ContainerBuilder $container): void
    {
        $definition = new Definition(TurnipPatternPolicy::class, [
            new Reference(self::STEP_METHOD_NAME_SUGGESTER_ID),
        ]);
        $definition->addTag(self::PATTERN_POLICY_TAG, ['priority' => 50]);
        $container->setDefinition(self::PATTERN_POLICY_TAG . '.turnip', $definition);

        $definition = new Definition(RegexPatternPolicy::class, [
            new Reference(self::STEP_METHOD_NAME_SUGGESTER_ID),
        ]);
        $definition->addTag(self::PATTERN_POLICY_TAG, ['priority' => 60]);
        $container->setDefinition(self::PATTERN_POLICY_TAG . '.regex', $definition);
    }

    /**
     * Loads definition Attribute reader.
     */
    private function loadAttributeReader(ContainerBuilder $container): void
    {
        $definition = new Definition(DefinitionAttributeReader::class, [
            new Reference(self::DOC_BLOCK_HELPER_ID),
        ]);
        $definition->addTag(ContextExtension::ATTRIBUTE_READER_TAG, ['priority' => 50]);
        $container->setDefinition(ContextExtension::ATTRIBUTE_READER_TAG . '.definition', $definition);
    }

    /**
     * Loads definition printers.
     */
    private function loadDefinitionPrinters(ContainerBuilder $container): void
    {
        $definition = new Definition(ConsoleDefinitionInformationPrinter::class, [
            new Reference(CliExtension::OUTPUT_ID),
            new Reference(self::DEFINITION_TRANSLATOR_ID),
            new Reference(GherkinExtension::KEYWORDS_ID),
        ]);
        $container->setDefinition($this->getInformationPrinterId(), $definition);

        $definition = new Definition(ConsoleDefinitionListPrinter::class, [
            new Reference(CliExtension::OUTPUT_ID),
            new Reference(self::DEFINITION_TRANSLATOR_ID),
            new Reference(GherkinExtension::KEYWORDS_ID),
        ]);
        $container->setDefinition($this->getListPrinterId(), $definition);
    }

    private function loadControllers(ContainerBuilder $container, bool $printUnusedDefinitions): void
    {
        $definition = new Definition(AvailableDefinitionsController::class, [
            new Reference(SuiteExtension::REGISTRY_ID),
            new Reference(self::WRITER_ID),
            new Reference($this->getListPrinterId()),
            new Reference($this->getInformationPrinterId()),
        ]);
        $definition->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 500]);
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.available_definitions', $definition);

        $definition = new Definition(UnusedDefinitionsController::class, [
            new Reference(self::REPOSITORY_ID),
            new Reference(EventDispatcherExtension::DISPATCHER_ID),
            new Reference($this->getInformationPrinterId()),
            $printUnusedDefinitions,
        ]);
        $definition->addTag(CliExtension::CONTROLLER_TAG, ['priority' => 300]);
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.unused_definitions', $definition);
    }

    /**
     * Loads DocBlockHelper.
     */
    private function loadDocblockHelper(ContainerBuilder $container): void
    {
        $definition = new Definition(DocBlockHelper::class);

        $container->setDefinition(self::DOC_BLOCK_HELPER_ID, $definition);
    }

    /**
     * Processes all search engines in the container.
     */
    private function processSearchEngines(ContainerBuilder $container): void
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::SEARCH_ENGINE_TAG);
        $definition = $container->getDefinition(self::FINDER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerSearchEngine', [$reference]);
        }
    }

    /**
     * Processes all pattern policies.
     */
    private function processPatternPolicies(ContainerBuilder $container): void
    {
        $references = $this->processor->findAndSortTaggedServices($container, self::PATTERN_POLICY_TAG);
        $definition = $container->getDefinition(self::PATTERN_TRANSFORMER_ID);

        foreach ($references as $reference) {
            $definition->addMethodCall('registerPatternPolicy', [$reference]);
        }
    }

    /**
     * returns list printer service id.
     */
    private function getListPrinterId(): string
    {
        return 'definition.list_printer';
    }

    /**
     * Returns information printer service id.
     */
    private function getInformationPrinterId(): string
    {
        return 'definition.information_printer';
    }
}

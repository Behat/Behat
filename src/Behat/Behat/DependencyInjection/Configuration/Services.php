<?php

namespace Behat\Behat\DependencyInjection\Configuration;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This class contains the DIC configuration for the Behat
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Services
{
    /**
     * Process container.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $this->setDefaultParameters($container);
        $this->registerConsoleServices($container);
        $this->registerCalleeServices($container);
        $this->registerContextServices($container);
        $this->registerDefinitionServices($container);
        $this->registerFeaturesServices($container);
        $this->registerOutputServices($container);
        $this->registerGherkinServices($container);
        $this->registerHookServices($container);
        $this->registerSnippetServices($container);
        $this->registerSuiteServices($container);
        $this->registerTransformationServices($container);
        $this->registerRunControlServices($container);
        $this->registerTesterServices($container);
        $this->registerClassLoader($container);
        $this->registerEventDispatcher($container);
        $this->registerTranslator($container);
    }

    private function setDefaultParameters(ContainerBuilder $container)
    {
        $container->setParameter('paths.base', null);
        $container->setParameter('paths.lib', null);
        $container->setParameter('paths.i18n', '%paths.lib%/i18n.php');
        $container->setParameter('paths.gherkin.lib', null);
        $container->setParameter('paths.gherkin.i18n', '%paths.gherkin.lib%/i18n.php');

        $container->setParameter('options.strict', false);
        $container->setParameter('options.dry_run', false);
        $container->setParameter('options.stop_on_failure', false);
        $container->setParameter('options.append_snippets', false);
        $container->setParameter('options.error_reporting', E_ALL);

        $container->setParameter('extension.classes', array());
    }

    private function registerConsoleServices(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Console\BehatCommand', array(array()));
        $container->setDefinition('console.command', $definition);

        $definition = new Definition('Behat\Behat\Console\Processor\AppendSnippetsProcessor', array(
            new Reference('snippet.use_case.append_context_snippets'),
            new Reference('output.manager')
        ));
        $definition->addTag('console.processor');
        $container->setDefinition('console.processor.append_snippets', $definition);

        $definition = new Definition('Behat\Behat\Console\Processor\PrintDefinitionsProcessor', array(
            new Reference('event_dispatcher'),
            new Reference('definition.use_case.print_definitions')
        ));
        $definition->addTag('console.processor');
        $container->setDefinition('console.processor.print_definitions', $definition);

        $definition = new Definition('Behat\Behat\Console\Processor\RerunProcessor', array(
            new Reference('run_control.use_case.cache_failed_scenarios_for_rerun')
        ));
        $definition->addTag('console.processor');
        $container->setDefinition('console.processor.rerun', $definition);

        $definition = new Definition('Behat\Behat\Console\Processor\RunProcessor', array(
            new Reference('event_dispatcher'),
            '%options.dry_run%',
            '%options.strict%'
        ));
        $definition->addTag('console.processor');
        $container->setDefinition('console.processor.run', $definition);

        $definition = new Definition('Behat\Behat\Console\Processor\FormatProcessor', array(
            new Reference('output.manager'),
            new Reference('translator'),
            '%paths.i18n%'
        ));
        $definition->addTag('console.processor');
        $container->setDefinition('console.processor.format', $definition);

        $definition = new Definition('Behat\Behat\Console\Processor\GherkinFilterProcessor', array(
            new Reference('gherkin')
        ));
        $definition->addTag('console.processor');
        $container->setDefinition('console.processor.gherkin_filter', $definition);

        $definition = new Definition('Behat\Behat\Console\Processor\InitProcessor', array(
            new Reference('event_dispatcher'),
            new Reference('context.use_case.generate_context_class'),
            new Reference('class_loader'),
            '%paths.base%'
        ));
        $definition->addTag('console.processor');
        $container->setDefinition('console.processor.init', $definition);

        $definition = new Definition('Behat\Behat\Console\Processor\StopOnFailureProcessor', array(
            new Reference('run_control.use_case.stop_on_failure')
        ));
        $definition->addTag('console.processor');
        $container->setDefinition('console.processor.stop_on_failure', $definition);

        $definition = new Definition('Behat\Behat\Console\Processor\PrintStorySyntaxProcessor', array(
            new Reference('gherkin.use_case.print_syntax')
        ));
        $definition->addTag('console.processor');
        $container->setDefinition('console.processor.print_story_syntax', $definition);
    }

    private function registerCalleeServices(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Callee\UseCase\ExecuteCallee', array(
            '%options.error_reporting%'
        ));
        $definition->addTag('event_subscriber');
        $container->setDefinition('callee.use_case.execute_callee', $definition);
    }

    private function registerContextServices(ContainerBuilder $container)
    {
        $readerRef = new Reference('context.callees_reader');
        $translatorRef = new Reference('translator');

        $definition = new Definition('Behat\Behat\Context\UseCase\CreateContextPool');
        $definition->addTag('event_subscriber');
        $container->setDefinition('context.use_case.create_context_pool', $definition);

        $definition = new Definition('Behat\Behat\Context\UseCase\InitializeContextPool');
        $definition->addTag('event_subscriber');
        $container->setDefinition('context.use_case.initialize_context_pool', $definition);

        $definition = new Definition('Behat\Behat\Context\UseCase\GenerateContextClass');
        $container->setDefinition('context.use_case.generate_context_class', $definition);

        $definition = new Definition('Behat\Behat\Context\Reader\CachedReader');
        $container->setDefinition('context.callees_reader', $definition);

        $definition = new Definition('Behat\Behat\Context\Loader\AnnotatedContextLoader');
        $definition->addTag('context.loader');
        $container->setDefinition('context.loader.annotated', $definition);

        $definition = new Definition('Behat\Behat\Context\Loader\TranslatableContextLoader', array($translatorRef));
        $definition->addTag('context.loader');
        $container->setDefinition('context.loader.translatable', $definition);

        $definition = new Definition('Behat\Behat\Context\Generator\DefaultContextGenerator');
        $definition->addTag('context.generator');
        $container->setDefinition('context.generator.default', $definition);
    }

    private function registerDefinitionServices(ContainerBuilder $container)
    {
        $eventDispatcherRef = new Reference('event_dispatcher');
        $translatorRef = new Reference('translator');
        $readerRef = new Reference('context.callees_reader');

        $definition = new Definition('Behat\Behat\Definition\UseCase\FindDefinition', array(
            $eventDispatcherRef,
            $translatorRef
        ));
        $definition->addTag('event_subscriber');
        $container->setDefinition('definition.use_case.find_definition', $definition);

        $definition = new Definition('Behat\Behat\Definition\UseCase\PrintDefinitions', array(
            $eventDispatcherRef,
            $translatorRef
        ));
        $container->setDefinition('definition.use_case.print_definitions', $definition);

        $definition = new Definition('Behat\Behat\Definition\Context\DefinitionAnnotationReader');
        $definition->addTag('context.annotation_reader');
        $container->setDefinition('context.annotation_reader.definition', $definition);

        $definition = new Definition('Behat\Behat\Definition\UseCase\LoadContextDefinitions', array($readerRef));
        $definition->addTag('event_subscriber');
        $container->setDefinition('definition.use_case.load_context_definitions', $definition);
    }

    private function registerFeaturesServices(ContainerBuilder $container)
    {
        $eventDispatcherRef = new Reference('event_dispatcher');
        $gherkinRef = new Reference('gherkin');

        $definition = new Definition('Behat\Behat\Features\UseCase\LoadFeatures', array($eventDispatcherRef));
        $definition->addTag('event_subscriber');
        $container->setDefinition('features.use_case.load_features', $definition);

        $definition = new Definition('Behat\Behat\Features\Loader\GherkinLoader', array($gherkinRef));
        $definition->addTag('features.loader');
        $container->setDefinition('features.loader.gherkin', $definition);
    }

    private function registerOutputServices(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Output\OutputManager', array(
            new Reference('event_dispatcher')
        ));
        $container->setDefinition('output.manager', $definition);

        $definition = new Definition('Behat\Behat\Output\Formatter\PrettyFormatter', array(
            new Reference('run_control.use_case.collect_statistics'),
            new Reference('snippet.context_snippet_repository'),
            new Reference('translator')
        ));
        $definition->addMethodCall('setParameter', array(
            'base_path',
            '%paths.base%'
        ));
        $definition->addTag('output.formatter');
        $container->setDefinition('output.formatter.pretty', $definition);

        $definition = new Definition('Behat\Behat\Output\Formatter\ProgressFormatter', array(
            new Reference('run_control.use_case.collect_statistics'),
            new Reference('snippet.context_snippet_repository'),
            new Reference('translator')
        ));
        $definition->addMethodCall('setParameter', array(
            'base_path',
            '%paths.base%'
        ));
        $definition->addTag('output.formatter');
        $container->setDefinition('output.formatter.progress', $definition);

        $definition = new Definition('Behat\Behat\Output\Formatter\HtmlFormatter', array(
            new Reference('run_control.use_case.collect_statistics'),
            new Reference('snippet.context_snippet_repository'),
            new Reference('translator')
        ));
        $definition->addMethodCall('setParameter', array(
            'base_path',
            '%paths.base%'
        ));
        $definition->addTag('output.formatter');
        $container->setDefinition('output.formatter.html', $definition);

        $definition = new Definition('Behat\Behat\Output\Formatter\JunitFormatter', array(
            new Reference('run_control.use_case.collect_statistics'),
            new Reference('snippet.context_snippet_repository'),
            new Reference('translator')
        ));
        $definition->addMethodCall('setParameter', array(
            'base_path',
            '%paths.base%'
        ));
        $definition->addTag('output.formatter');
        $container->setDefinition('output.formatter.junit', $definition);

        $definition = new Definition('Behat\Behat\Output\Formatter\FailedScenariosFormatter', array(
            new Reference('run_control.use_case.collect_statistics'),
            new Reference('snippet.context_snippet_repository'),
            new Reference('translator')
        ));
        $definition->addMethodCall('setParameter', array(
            'base_path',
            '%paths.base%'
        ));
        $definition->addTag('output.formatter');
        $container->setDefinition('output.formatter.failed_scenarios', $definition);

        $definition = new Definition('Behat\Behat\Output\Formatter\SnippetsFormatter', array(
            new Reference('run_control.use_case.collect_statistics'),
            new Reference('snippet.context_snippet_repository'),
            new Reference('translator')
        ));
        $definition->addMethodCall('setParameter', array(
            'base_path',
            '%paths.base%'
        ));
        $definition->addTag('output.formatter');
        $container->setDefinition('output.formatter.snippets', $definition);
    }

    private function registerGherkinServices(ContainerBuilder $container)
    {
        $gherkinRef = new Reference('gherkin');
        $lexerRef = new Reference('gherkin.lexer');
        $parserRef = new Reference('gherkin.parser');
        $keywordsRef = new Reference('gherkin.keywords');

        $definition = new Definition('Behat\Gherkin\Gherkin');
        $container->setDefinition('gherkin', $definition);

        $definition = new Definition('Behat\Gherkin\Parser', array($lexerRef));
        $container->setDefinition('gherkin.parser', $definition);

        $definition = new Definition('Behat\Gherkin\Lexer', array($keywordsRef));
        $container->setDefinition('gherkin.lexer', $definition);

        $definition = new Definition('Behat\Gherkin\Keywords\CachedArrayKeywords', array('%paths.gherkin.i18n%'));
        $container->setDefinition('gherkin.keywords', $definition);

        $definition = new Definition('Behat\Behat\Gherkin\UseCase\PrintSyntax', array(
            new Definition('Behat\Gherkin\Keywords\KeywordsDumper', array($keywordsRef))
        ));
        $container->setDefinition('gherkin.use_case.print_syntax', $definition);

        $definition = new Definition('Behat\Gherkin\Loader\DirectoryLoader', array($gherkinRef));
        $definition->addTag('gherkin.loader');
        $container->setDefinition('gherkin.loader.directory', $definition);

        $definition = new Definition('Behat\Gherkin\Loader\GherkinFileLoader', array($parserRef));
        $definition->addMethodCall('setCache', array(
            new Definition('Behat\Gherkin\Cache\MemoryCache')
        ));
        $definition->addTag('gherkin.loader');
        $container->setDefinition('gherkin.loader.gherkin_file', $definition);
    }

    private function registerHookServices(ContainerBuilder $container)
    {
        $eventDispatcherRef = new Reference('event_dispatcher');
        $readerRef = new Reference('context.callees_reader');

        $definition = new Definition('Behat\Behat\Hook\UseCase\DispatchHooks', array($eventDispatcherRef));
        $definition->addTag('event_subscriber');
        $container->setDefinition('hook.use_case.dispatch_hooks', $definition);

        $definition = new Definition('Behat\Behat\Hook\UseCase\LoadContextHooks', array($readerRef));
        $definition->addTag('event_subscriber');
        $container->setDefinition('hook.use_case.load_context_hooks', $definition);

        $definition = new Definition('Behat\Behat\Hook\Context\HookAnnotationReader');
        $definition->addTag('context.annotation_reader');
        $container->setDefinition('context.annotation_reader.hook', $definition);
    }

    private function registerSnippetServices(ContainerBuilder $container)
    {
        $snippetsRepositoryRef = new Reference('snippet.context_snippet_repository');

        $definition = new Definition('Behat\Behat\Snippet\UseCase\CreateSnippet', array(
            $snippetsRepositoryRef
        ));
        $definition->addTag('event_subscriber');
        $container->setDefinition('snippet.use_case.create_snippet', $definition);

        $definition = new Definition('Behat\Behat\Snippet\Generator\ContextTurnipSnippetGenerator');
        $definition->addTag('snippet.generator');
        $container->setDefinition('snippet.generator.context_turnip', $definition);

        $definition = new Definition('Behat\Behat\Snippet\Generator\ContextRegexSnippetGenerator');
        $definition->addTag('snippet.generator');
        $container->setDefinition('snippet.generator.context_regex', $definition);

        $definition = new Definition('Behat\Behat\Snippet\ContextSnippetRepository');
        $container->setDefinition('snippet.context_snippet_repository', $definition);

        $definition = new Definition('Behat\Behat\Snippet\UseCase\AppendContextSnippets', array(
            $snippetsRepositoryRef,
            '%options.append_snippets%'
        ));
        $definition->addTag('event_subscriber');
        $container->setDefinition('snippet.use_case.append_context_snippets', $definition);
    }

    private function registerSuiteServices(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Suite\UseCase\LoadSuites');
        $definition->addTag('event_subscriber');
        $container->setDefinition('suite.use_case.load_suites', $definition);

        $definition = new Definition('Behat\Behat\Suite\SuiteFactory');
        $container->setDefinition('suite.suite_factory', $definition);

        $definition = new Definition('Behat\Behat\Suite\Generator\GherkinSuiteGenerator', array(
            array(
                'paths'    => array('%paths.base%/features'),
                'contexts' => array('FeatureContext')
            )
        ));
        $definition->addTag('suite.generator');
        $container->setDefinition('suite.generator.gherkin', $definition);
    }

    private function registerTransformationServices(ContainerBuilder $container)
    {
        $readerRef = new Reference('context.callees_reader');

        $definition = new Definition('Behat\Behat\Transformation\UseCase\TransformArguments', array(
            new Reference('event_dispatcher'),
            new Reference('translator')
        ));
        $definition->addTag('event_subscriber');
        $container->setDefinition('transformer.use_case.transform_arguments', $definition);

        $definition = new Definition('Behat\Behat\Transformation\UseCase\LoadContextTransformations', array($readerRef));
        $definition->addTag('event_subscriber');
        $container->setDefinition('transformations.use_case.load_context_transformations', $definition);

        $definition = new Definition('Behat\Behat\Transformation\Context\TransformationAnnotationReader');
        $definition->addTag('context.annotation_reader');
        $container->setDefinition('context.annotation_reader.transformation', $definition);
    }

    private function registerRunControlServices(ContainerBuilder $container)
    {
        $eventDispatcherRef = new Reference('event_dispatcher');

        $definition = new Definition('Behat\Behat\RunControl\UseCase\ProperlyAbortOnSigint', array(
            $eventDispatcherRef
        ));
        $definition->addTag('event_subscriber');
        $container->setDefinition('run_control.use_case.properly_stop_on_sigint', $definition);

        $definition = new Definition('Behat\Behat\RunControl\UseCase\StopOnFirstFailure', array(
            $eventDispatcherRef,
            '%options.stop_on_failure%'
        ));
        $definition->addTag('event_subscriber');
        $container->setDefinition('run_control.use_case.stop_on_failure', $definition);

        $definition = new Definition('Behat\Behat\RunControl\UseCase\CacheFailedScenariosForRerun');
        $definition->addTag('event_subscriber');
        $container->setDefinition('run_control.use_case.cache_failed_scenarios_for_rerun', $definition);

        $definition = new Definition('Behat\Behat\RunControl\UseCase\CollectStatistics');
        $definition->addTag('event_subscriber');
        $container->setDefinition('run_control.use_case.collect_statistics', $definition);
    }

    private function registerTesterServices(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Tester\EventSubscriber\TesterDispatcher', array(
            new Reference('tester.exercise'),
            new Reference('tester.suite'),
            new Reference('tester.feature'),
            new Reference('tester.background'),
            new Reference('tester.scenario'),
            new Reference('tester.outline'),
            new Reference('tester.example'),
            new Reference('tester.step')
        ));
        $definition->addTag('event_subscriber');
        $container->setDefinition('tester.tester_dispatcher', $definition);

        $definition = new Definition('Behat\Behat\Tester\ExerciseTester', array(
            new Reference('event_dispatcher')
        ));
        $container->setDefinition('tester.exercise', $definition);

        $definition = new Definition('Behat\Behat\Tester\SuiteTester', array(
            new Reference('event_dispatcher')
        ));
        $container->setDefinition('tester.suite', $definition);

        $definition = new Definition('Behat\Behat\Tester\FeatureTester', array(
            new Reference('event_dispatcher')
        ));
        $container->setDefinition('tester.feature', $definition);

        $definition = new Definition('Behat\Behat\Tester\BackgroundTester', array(
            new Reference('event_dispatcher')
        ));
        $container->setDefinition('tester.background', $definition);

        $definition = new Definition('Behat\Behat\Tester\ScenarioTester', array(
            new Reference('event_dispatcher')
        ));
        $container->setDefinition('tester.scenario', $definition);

        $definition = new Definition('Behat\Behat\Tester\OutlineTester', array(
            new Reference('event_dispatcher')
        ));
        $container->setDefinition('tester.outline', $definition);

        $definition = new Definition('Behat\Behat\Tester\ExampleTester', array(
            new Reference('event_dispatcher')
        ));
        $container->setDefinition('tester.example', $definition);

        $definition = new Definition('Behat\Behat\Tester\StepTester', array(
            new Reference('event_dispatcher')
        ));
        $container->setDefinition('tester.step', $definition);
    }

    private function registerClassLoader(ContainerBuilder $container)
    {
        $definition = new Definition('Symfony\Component\ClassLoader\ClassLoader');
        $definition->addMethodCall('register');
        $container->setDefinition('class_loader', $definition);
    }

    private function registerEventDispatcher(ContainerBuilder $container)
    {
        $definition = new Definition('Symfony\Component\EventDispatcher\EventDispatcher');
        $container->setDefinition('event_dispatcher', $definition);
    }

    private function registerTranslator(ContainerBuilder $container)
    {
        $definition = new Definition('Symfony\Component\Translation\Translator', array(
            'en'
        ));
        $definition->addMethodCall('setFallbackLocale', array('en'));
        $definition->addMethodCall('addLoader', array(
            'xliff',
            new Definition('Symfony\Component\Translation\Loader\XliffFileLoader')
        ));
        $definition->addMethodCall('addLoader', array(
            'yaml',
            new Definition('Symfony\Component\Translation\Loader\YamlFileLoader')
        ));
        $definition->addMethodCall('addLoader', array(
            'php',
            new Definition('Symfony\Component\Translation\Loader\PhpFileLoader')
        ));
        $definition->addMethodCall('addLoader', array(
            'array',
            new Definition('Symfony\Component\Translation\Loader\ArrayLoader')
        ));
        $container->setDefinition('translator', $definition);
    }
}

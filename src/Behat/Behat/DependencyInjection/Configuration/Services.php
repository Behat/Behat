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
        $this->registerTransformerServices($container);
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
        $definition = new Definition('Behat\Behat\Console\BehatCommand', array(
            array()
        ));
        $container->setDefinition('console.command', $definition);

        $definition = new Definition('Behat\Behat\Console\Processor\AppendSnippetsProcessor', array(
            new Reference('snippet.context_snippets_appender'),
            new Reference('output.manager')
        ));
        $definition->addTag('console.processor');
        $container->setDefinition('console.processor.append_snippets', $definition);

        $definition = new Definition('Behat\Behat\Console\Processor\DefinitionsPrinterProcessor', array(
            new Reference('event_dispatcher'),
            new Reference('definition.printer')
        ));
        $definition->addTag('console.processor');
        $container->setDefinition('console.processor.definitions_printer', $definition);

        $definition = new Definition('Behat\Behat\Console\Processor\RerunProcessor', array(
            new Reference('run_control.cache_failed_scenarios_for_rerun')
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
            new Reference('class_loader'),
            '%paths.base%'
        ));
        $definition->addTag('console.processor');
        $container->setDefinition('console.processor.init', $definition);

        $definition = new Definition('Behat\Behat\Console\Processor\StopOnFailureProcessor', array(
            new Reference('run_control.stop_on_failure')
        ));
        $definition->addTag('console.processor');
        $container->setDefinition('console.processor.stop_on_failure', $definition);

        $definition = new Definition('Behat\Behat\Console\Processor\StorySyntaxPrinterProcessor', array(
            new Reference('gherkin.printer')
        ));
        $definition->addTag('console.processor');
        $container->setDefinition('console.processor.story_syntax_printer', $definition);
    }

    private function registerCalleeServices(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Callee\EventSubscriber\CalleeExecutor', array(
            '%options.error_reporting%'
        ));
        $definition->addTag('event_subscriber');
        $container->setDefinition('callee.executor', $definition);
    }

    private function registerContextServices(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Context\EventSubscriber\ContextPoolFactory');
        $definition->addTag('event_subscriber');
        $container->setDefinition('context.pool_factory', $definition);

        $definition = new Definition('Behat\Behat\Context\EventSubscriber\ContextPoolInitializer');
        $definition->addTag('event_subscriber');
        $container->setDefinition('context.pool_initializer', $definition);

        $definition = new Definition('Behat\Behat\Context\EventSubscriber\DictionaryReader', array(
            new Reference('context.callees_reader')
        ));
        $definition->addTag('event_subscriber');
        $container->setDefinition('context.dictionary_reader', $definition);

        $definition = new Definition('Behat\Behat\Context\Reader\CachedReader');
        $container->setDefinition('context.callees_reader', $definition);

        $definition = new Definition('Behat\Behat\Context\Reader\Loader\AnnotatedContextLoader');
        $definition->addTag('context.loader');
        $container->setDefinition('context.loader.annotated', $definition);

        $definition = new Definition('Behat\Behat\Context\Reader\Loader\TranslatableContextLoader', array(
            new Reference('translator')
        ));
        $definition->addTag('context.loader');
        $container->setDefinition('context.loader.translatable', $definition);
    }

    private function registerDefinitionServices(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Definition\EventSubscriber\DefinitionFinder', array(
            new Reference('event_dispatcher'),
            new Reference('translator')
        ));
        $definition->addTag('event_subscriber');
        $container->setDefinition('definition.finder', $definition);

        $definition = new Definition('Behat\Behat\Definition\Support\DefinitionsPrinter', array(
            new Reference('event_dispatcher'),
            new Reference('translator')
        ));
        $container->setDefinition('definition.printer', $definition);
    }

    private function registerFeaturesServices(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Features\EventSubscriber\FeaturesLoader', array(
            new Reference('event_dispatcher')
        ));
        $definition->addTag('event_subscriber');
        $container->setDefinition('features.features_loader', $definition);

        $definition = new Definition('Behat\Behat\Features\Loader\GherkinLoader', array(
            new Reference('gherkin')
        ));
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
            new Reference('tester.statistics_collector'),
            new Reference('snippet.snippets_collector'),
            new Reference('translator')
        ));
        $definition->addMethodCall('setParameter', array(
            'base_path',
            '%paths.base%'
        ));
        $definition->addTag('output.formatter');
        $container->setDefinition('output.formatter.pretty', $definition);

        $definition = new Definition('Behat\Behat\Output\Formatter\ProgressFormatter', array(
            new Reference('tester.statistics_collector'),
            new Reference('snippet.snippets_collector'),
            new Reference('translator')
        ));
        $definition->addMethodCall('setParameter', array(
            'base_path',
            '%paths.base%'
        ));
        $definition->addTag('output.formatter');
        $container->setDefinition('output.formatter.progress', $definition);

        $definition = new Definition('Behat\Behat\Output\Formatter\FailedScenariosFormatter', array(
            new Reference('tester.statistics_collector'),
            new Reference('snippet.snippets_collector'),
            new Reference('translator')
        ));
        $definition->addMethodCall('setParameter', array(
            'base_path',
            '%paths.base%'
        ));
        $definition->addTag('output.formatter');
        $container->setDefinition('output.formatter.failed_scenarios', $definition);

        $definition = new Definition('Behat\Behat\Output\Formatter\SnippetsFormatter', array(
            new Reference('tester.statistics_collector'),
            new Reference('snippet.snippets_collector'),
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
        $definition = new Definition('Behat\Gherkin\Gherkin');
        $container->setDefinition('gherkin', $definition);

        $definition = new Definition('Behat\Gherkin\Parser', array(
            new Reference('gherkin.lexer')
        ));
        $container->setDefinition('gherkin.parser', $definition);

        $definition = new Definition('Behat\Gherkin\Lexer', array(
            new Reference('gherkin.keywords')
        ));
        $container->setDefinition('gherkin.lexer', $definition);

        $definition = new Definition('Behat\Gherkin\Keywords\CachedArrayKeywords', array(
            '%paths.gherkin.i18n%'
        ));
        $container->setDefinition('gherkin.keywords', $definition);

        $definition = new Definition('Behat\Behat\Gherkin\Support\SyntaxPrinter', array(
            new Definition('Behat\Gherkin\Keywords\KeywordsDumper', array(
                new Reference('gherkin.keywords')
            ))
        ));
        $container->setDefinition('gherkin.printer', $definition);

        $definition = new Definition('Behat\Gherkin\Loader\DirectoryLoader', array(
            new Reference('gherkin')
        ));
        $definition->addTag('gherkin.loader');
        $container->setDefinition('gherkin.loader.directory', $definition);

        $definition = new Definition('Behat\Gherkin\Loader\GherkinFileLoader', array(
            new Reference('gherkin.parser')
        ));
        $definition->addMethodCall('setCache', array(
            new Definition('Behat\Gherkin\Cache\MemoryCache')
        ));
        $definition->addTag('gherkin.loader');
        $container->setDefinition('gherkin.loader.gherkin_file', $definition);
    }

    private function registerHookServices(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Hook\EventSubscriber\HookDispatcher', array(
            new Reference('event_dispatcher')
        ));
        $definition->addTag('event_subscriber');
        $container->setDefinition('hook.hook_dispatcher', $definition);
    }

    private function registerSnippetServices(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Snippet\EventSubscriber\ContextSnippetGenerator');
        $definition->addTag('event_subscriber');
        $container->setDefinition('snippet.snippet_generator', $definition);

        $definition = new Definition('Behat\Behat\Snippet\EventSubscriber\SnippetsCollector');
        $definition->addTag('event_subscriber');
        $container->setDefinition('snippet.snippets_collector', $definition);

        $definition = new Definition('Behat\Behat\Snippet\EventSubscriber\ContextSnippetsAppender', array(
            new Reference('snippet.snippets_collector'),
            '%options.append_snippets%'
        ));
        $definition->addTag('event_subscriber');
        $container->setDefinition('snippet.context_snippets_appender', $definition);
    }

    private function registerSuiteServices(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Suite\EventSubscriber\SuitesLoader');
        $definition->addTag('event_subscriber');
        $container->setDefinition('suite.suites_loader', $definition);

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

    private function registerTransformerServices(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Transformation\EventSubscriber\ArgumentsTransformer', array(
            new Reference('event_dispatcher'),
            new Reference('translator')
        ));
        $definition->addTag('event_subscriber');
        $container->setDefinition('transformer.arguments_transformer', $definition);
    }

    private function registerRunControlServices(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\RunControl\EventSubscriber\ProperlyAbortOnSigint', array(
            new Reference('event_dispatcher')
        ));
        $definition->addTag('event_subscriber');
        $container->setDefinition('run_control.properly_stop_on_sigint', $definition);

        $definition = new Definition('Behat\Behat\RunControl\EventSubscriber\StopOnFirstFailure', array(
            new Reference('event_dispatcher'),
            '%options.stop_on_failure%'
        ));
        $definition->addTag('event_subscriber');
        $container->setDefinition('run_control.stop_on_failure', $definition);

        $definition = new Definition('Behat\Behat\RunControl\EventSubscriber\CacheFailedScenariosForRerun');
        $definition->addTag('event_subscriber');
        $container->setDefinition('run_control.cache_failed_scenarios_for_rerun', $definition);
    }

    private function registerTesterServices(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Tester\EventSubscriber\StatisticsCollector');
        $definition->addTag('event_subscriber');
        $container->setDefinition('tester.statistics_collector', $definition);

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

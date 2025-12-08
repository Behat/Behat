<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\StringableForToStringRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\BoolReturnTypeFromBooleanStrictReturnsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\NumericReturnTypeFromStrictReturnsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictConstantReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector;
use Rector\TypeDeclaration\Rector\ClassMethod\StringReturnTypeFromStrictScalarReturnsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\StringReturnTypeFromStrictStringReturnsRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/features',
        __DIR__ . '/src',
    ])
    ->withRootFiles()
    ->withPreparedSets(codeQuality: true)
    ->withPhpSets(php81: true)
    ->withTypeCoverageLevel(21)
    ->withSkip([
        StringableForToStringRector::class,
        ReturnTypeFromStrictConstantReturnRector::class => [
            // Would be a BC break
            __DIR__.'/src/Behat/Behat/Transformation/ServiceContainer/TransformationExtension.php',
        ],
        StringReturnTypeFromStrictScalarReturnsRector::class => [
            // Would be a BC break
            __DIR__.'/src/Behat/Behat/Snippet/ServiceContainer/SnippetExtension.php',
            __DIR__.'/src/Behat/Behat/Transformation/ServiceContainer/TransformationExtension.php',
            __DIR__.'/src/Behat/Testwork/EventDispatcher/ServiceContainer/EventDispatcherExtension.php',
            __DIR__.'/src/Behat/Testwork/Hook/ServiceContainer/HookExtension.php',
        ],
        BoolReturnTypeFromBooleanStrictReturnsRector::class => [
            // Would be a BC break
            __DIR__.'/src/Behat/Behat/Tester/Exception/Stringer/PendingExceptionStringer.php',
            __DIR__.'/src/Behat/Testwork/Call/RuntimeCallee.php',
        ],
        StringReturnTypeFromStrictStringReturnsRector::class => [
            // Would be a BC break
            __DIR__.'/src/Behat/Behat/Output/Statistics/StepStat.php',
            __DIR__.'/src/Behat/Behat/Tester/Exception/Stringer/PendingExceptionStringer.php',
            __DIR__.'/src/Behat/Behat/Transformation/ServiceContainer/TransformationExtension.php',
        ],
        NumericReturnTypeFromStrictReturnsRector::class => [
            // Would be a BC break
            __DIR__.'/src/Behat/Behat/Output/Statistics/StepStat.php',
        ],
        ReturnTypeFromReturnNewRector::class => [
            // Would be a BC break
            __DIR__.'/src/Behat/Behat/Output/ServiceContainer/Formatter/PrettyFormatterFactory.php',
            __DIR__.'/src/Behat/Behat/Output/ServiceContainer/Formatter/ProgressFormatterFactory.php',
            __DIR__.'/src/Behat/Behat/Transformation/ServiceContainer/TransformationExtension.php',
            __DIR__.'/src/Behat/Testwork/Output/Printer/Factory/ConsoleOutputFactory.php',
            __DIR__.'/src/Behat/Testwork/Output/Printer/Factory/FilesystemOutputFactory.php',
        ],
        AddVoidReturnTypeWhereNoReturnRector::class => [
            // Would be a BC break
            __DIR__.'/src/Behat/Behat/EventDispatcher/ServiceContainer/EventDispatcherExtension.php',
            __DIR__.'/src/Behat/Behat/Output/Node/EventListener/Flow/FireOnlySiblingsListener.php',
            __DIR__.'/src/Behat/Behat/Output/Node/EventListener/Flow/FirstBackgroundFiresFirstListener.php',
            __DIR__.'/src/Behat/Behat/Output/Node/EventListener/Flow/OnlyFirstBackgroundFiresListener.php',
            __DIR__.'/src/Behat/Behat/Output/Node/Printer/JUnit/JUnitSetupPrinter.php',
            __DIR__.'/src/Behat/Behat/Output/Node/Printer/JUnit/JUnitStepPrinter.php',
            __DIR__.'/src/Behat/Behat/Output/ServiceContainer/Formatter/PrettyFormatterFactory.php',
            __DIR__.'/src/Behat/Behat/Output/ServiceContainer/Formatter/ProgressFormatterFactory.php',
            __DIR__.'/src/Behat/Behat/Snippet/Printer/ConsoleSnippetPrinter.php',
            __DIR__.'/src/Behat/Behat/Snippet/ServiceContainer/SnippetExtension.php',
            __DIR__.'/src/Behat/Behat/Tester/ServiceContainer/TesterExtension.php',
            __DIR__.'/src/Behat/Behat/Transformation/ServiceContainer/TransformationExtension.php',
            __DIR__.'/src/Behat/Config/Converter/ConfigConverterTools.php',
            __DIR__.'/src/Behat/Testwork/EventDispatcher/ServiceContainer/EventDispatcherExtension.php',
            __DIR__.'/src/Behat/Testwork/Hook/ServiceContainer/HookExtension.php',
            __DIR__.'/src/Behat/Testwork/Output/Cli/OutputController.php',
            __DIR__.'/src/Behat/Testwork/Output/Node/EventListener/ChainEventListener.php',
            __DIR__.'/src/Behat/Testwork/Output/Node/EventListener/Flow/FireOnlyIfFormatterParameterListener.php',
            __DIR__.'/src/Behat/Testwork/Output/Printer/Factory/FilesystemOutputFactory.php',
            __DIR__.'/src/Behat/Testwork/Output/Printer/Factory/OutputFactory.php',
            __DIR__.'/src/Behat/Testwork/Output/Printer/StreamOutputPrinter.php',
            __DIR__.'/src/Behat/Testwork/PathOptions/Cli/PathOptionsController.php',
            __DIR__.'/src/Behat/Testwork/Tester/ServiceContainer/TesterExtension.php',
        ],
        ReturnTypeFromStrictTypedPropertyRector::class => [
            // Would be a BC break
            __DIR__.'/src/Behat/Behat/Output/Statistics/StepStat.php',
            __DIR__.'/src/Behat/Config/Converter/UsedClassesCollector.php',
            __DIR__.'/src/Behat/Testwork/Call/RuntimeCallee.php',
            __DIR__.'/src/Behat/Testwork/Output/Printer/StreamOutputPrinter.php',
            // The interface for getScenario on these events is actually broken (says it returns ScenarioInterface
            // but it actually returns ScenarioLikeInterface) so we need to fix that (in 4.0) first
            __DIR__.'/src/Behat/Behat/EventDispatcher/Event/AfterBackgroundSetup.php',
            __DIR__.'/src/Behat/Behat/EventDispatcher/Event/AfterBackgroundTested.php',
            __DIR__.'/src/Behat/Behat/EventDispatcher/Event/BeforeBackgroundTeardown.php',
            __DIR__.'/src/Behat/Behat/EventDispatcher/Event/BeforeBackgroundTested.php',
            __DIR__.'/src/Behat/Behat/EventDispatcher/Event/AfterScenarioSetup.php',
            __DIR__.'/src/Behat/Behat/EventDispatcher/Event/AfterScenarioTested.php',
            __DIR__.'/src/Behat/Behat/EventDispatcher/Event/BeforeScenarioTeardown.php',
            __DIR__.'/src/Behat/Behat/EventDispatcher/Event/BeforeScenarioTested.php',
        ],
    ])
    ->withImportNames(
        removeUnusedImports: true,
    )
;

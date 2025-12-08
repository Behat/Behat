<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\StringableForToStringRector;
use Rector\TypeDeclaration\Rector\ClassMethod\BoolReturnTypeFromBooleanStrictReturnsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\NumericReturnTypeFromStrictReturnsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictConstantReturnRector;
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
    ->withTypeCoverageLevel(15)
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
    ])
    ->withImportNames(
        removeUnusedImports: true,
    )
;

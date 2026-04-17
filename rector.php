<?php

declare(strict_types=1);

use Ingenerator\RiskyRectorRules\AddStrictTypes\AddParamTypeBasedOnParentClassMethodRector;
use Ingenerator\RiskyRectorRules\PhpDocToStrictTypes\AddParamTypeFromPhpDocRector;
use Ingenerator\RiskyRectorRules\PhpDocToStrictTypes\AddReturnTypeFromPhpDocRector;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\StringableForToStringRector;
use Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationBasedOnParentClassMethodRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/features',
        __DIR__ . '/src',
    ])
    ->withRootFiles()
    ->withPreparedSets(
        codeQuality: true,
        typeDeclarations: true,
    )
    ->withPhpSets(php82: true)
    ->withSkip([
        StringableForToStringRector::class,
        ReadOnlyClassRector::class,
        // DI setFactory does not support closures
        ArrayToFirstClassCallableRector::class => [__DIR__ . '/src/Behat/Testwork/Deprecation/ServiceContainer/DeprecationExtension.php'],
    ])
    ->withImportNames(
        removeUnusedImports: true,
    )
    ->withRules([
        AddParamTypeFromPhpDocRector::class,
        AddReturnTypeFromPhpDocRector::class,
        AddReturnTypeDeclarationBasedOnParentClassMethodRector::class,
        AddParamTypeBasedOnParentClassMethodRector::class,
        ReadOnlyPropertyRector::class,
    ])
;

<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\StringableForToStringRector;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/features',
        __DIR__ . '/src',
    ])
    ->withRootFiles()
    ->withPreparedSets(codeQuality: true)
    ->withPhpSets(php82: true)
    ->withSkip([
        StringableForToStringRector::class,
        ReadOnlyClassRector::class,
        // DI setFactory does not support closures
        FirstClassCallableRector::class => [__DIR__ . '/src/Behat/Testwork/Deprecation/ServiceContainer/DeprecationExtension.php'],
    ])
    ->withImportNames(
        removeUnusedImports: true,
    )
;

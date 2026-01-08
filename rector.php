<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\StringableForToStringRector;
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
    ])
    ->withImportNames(
        removeUnusedImports: true,
    )
;

<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/features',
        __DIR__ . '/src',
    ])
    ->withRootFiles()
    ->withPreparedSets(codeQuality: true)
    ->withImportNames(
        removeUnusedImports: true,
    )
;

<?php

$hooks->beforeSuite(function($event) {
    if (is_dir($dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat')) {
        rmdir_recursive($dir);
    }
});

$hooks->beforeScenario('', function($event) {
    $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat' . DIRECTORY_SEPARATOR .
           md5(microtime() * rand(0, 10000));

    mkdir($dir, 0777, true);
    chdir($dir);
    mkdir('features');
    mkdir('features/steps');
    mkdir('features/steps/i18n');
    mkdir('features/support');
});

$hooks->afterSuite(function($event) {
    if (is_dir($dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat')) {
        rmdir_recursive($dir);
    }
});

function rmdir_recursive($dir) {
    $files = scandir($dir);
    array_shift($files);
    array_shift($files);

    foreach ($files as $file) {
        $file = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($file)) {
            rmdir_recursive($file);
        } else {
            unlink($file);
        }
    }

    rmdir($dir);
}

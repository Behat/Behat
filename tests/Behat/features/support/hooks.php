<?php

$hooks->before('suite.run', function($event) {
    if (is_dir($dir = sys_get_temp_dir() . '/behat/')) {
        system('rm -rf ' . $dir);
    }
});

$hooks->after('suite.run', function($event) {
    if (is_dir($dir = sys_get_temp_dir() . '/behat/')) {
        system('rm -rf ' . $dir);
    }
});

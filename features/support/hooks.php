<?php

$hooks->beforeSuite(function($event) {
    if (is_dir($dir = sys_get_temp_dir() . '/behat/')) {
        system('rm -rf ' . $dir);
    }
});

$hooks->afterSuite(function($event) {
    if (is_dir($dir = sys_get_temp_dir() . '/behat/')) {
        system('rm -rf ' . $dir);
    }
});

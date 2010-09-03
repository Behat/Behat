<?php

$steps->Given('/^a standard Behat project directory structure$/', function() use($world) {
    chdir(sys_get_temp_dir());

    if (is_dir('features')) {
        exec('rm -rf features');
    }

    mkdir('features');
    mkdir('features/steps');
    mkdir('features/support');
});

$steps->Given('/^a file named "([^"]*)" with:$/', function($filename, $content) use($world) {
    file_put_contents($filename, $content);
});

$steps->When('/^I run "([^"]*)"$/', function($command) use($world) {
    exec($command, $world->output, $world->return);
});

$steps->Then('/^it should (fail|pass) with:$/', function($success, $data) use($world) {
    if ('fail' === $success) {
        assertNotEquals(0, $world->return);
    } else {
        assertEquals(0, $world->return);
    }
    assertEquals(trim($data), trim(implode("\n", $world->output)));
});

$steps->Then('/^it should (fail|pass)$/', function($success) use($world) {
    if ('fail' === $success) {
        assertNotEquals(0, $world->return);
    } else {
        assertEquals(0, $world->return);
    }
});

$steps->Then('/^the output should contain$/', function($text) use($world) {
    assertContains($text, $world->output);
});

$steps->Then('/^the output should not contain$/', function($text) use($world) {
    assertNotContains($text, $world->output);
});

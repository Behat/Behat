<?php

$steps->Given('/^a standard Behat project directory structure$/', function($world) {
    $dir = sys_get_temp_dir() . '/behat/' . microtime(true);
    mkdir($dir, 0777, true);
    chdir($dir);

    if (is_dir('features')) {
        exec('rm -rf features');
    }

    mkdir('features');
    mkdir('features/steps');
    mkdir('features/support');
});

$steps->Given('/^a file named "([^"]*)" with:$/', function($world, $filename, $content) {
    file_put_contents($filename, strtr($content, array("'''" => '"""')));
});

$steps->When('/^I run "([^"]*)"$/', function($world, $command) {
    $world->command = $command;
    exec($command, $world->output, $world->return);

    // Remove formatting & time from output
    $world->output = preg_replace(array("/\n[0-9\.]+s/", "/\\033\[[^m]*m/", "/\\033\[0m/"), '',
        trim(implode("\n", $world->output))
    );
});

$steps->Then('/^display last command exit code$/', function($world) {
    $world->printDebug("`" . $world->command . "`  =>  " . $world->return);
});

$steps->Then('/^display last command output$/', function($world) {
    $world->printDebug("`" . $world->command . "`:\n" . $world->output);
});

$steps->Then('/^it should (fail|pass) with:$/', function($world, $success, $data) {
    if ('fail' === $success) {
        assertNotEquals(0, $world->return);
    } else {
        assertEquals(0, $world->return);
    }
    assertEquals($data, $world->output);
});

$steps->Then('/^it should (fail|pass)$/', function($world, $success) {
    if ('fail' === $success) {
        assertNotEquals(0, $world->return);
    } else {
        assertEquals(0, $world->return);
    }
});

$steps->Then('/^the output should contain$/', function($world, $text) {
    assertContains($text, $world->output);
});

$steps->Then('/^the output should not contain$/', function($world, $text) {
    assertNotContains($text, $world->output);
});

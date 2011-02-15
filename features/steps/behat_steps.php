<?php

$steps->Given('/^a file named "([^"]*)" with:$/', function($world, $filename, $content) {
    file_put_contents($filename, strtr($content, array("'''" => '"""')));
});

$steps->When('/^I run "behat ([^"]*)"$/', function($world, $command) {
    $world->command = $command;
    exec(BEHAT_BIN_PATH . ' ' . $command, $world->output, $world->return);
    $world->output = trim(implode("\n", $world->output));
});

$steps->Then('/^it should (fail|pass) with:$/', function($world, $success, $data) {
    if ('fail' === $success) {
        assertNotEquals(0, $world->return);
    } else {
        assertEquals(0, $world->return);
    }

    try {
        assertEquals((string) $data, $world->output);
    } catch (Exception $e) {
        $diff = PHPUnit_Framework_TestFailure::exceptionToString($e);
        throw new Exception($diff, $e->getCode(), $e);
    }
});

$steps->Then('/^it should (fail|pass)$/', function($world, $success) {
    if ('fail' === $success) {
        assertNotEquals(0, $world->return);
    } else {
        assertEquals(0, $world->return);
    }
});

$steps->Then('/^the output should contain:$/', function($world, $text) {
    try {
        assertContains((string) $text, $world->output);
    } catch (Exception $e) {
        $diff = PHPUnit_Framework_TestFailure::exceptionToString($e);
        throw new Exception($diff, $e->getCode(), $e);
    }
});

$steps->Then('/^the output should not contain:$/', function($world, $text) {
    try {
        assertNotContains((string) $text, $world->output);
    } catch (Exception $e) {
        $diff = PHPUnit_Framework_TestFailure::exceptionToString($e);
        throw new Exception($diff, $e->getCode(), $e);
    }
});

$steps->Then('/^display last command exit code$/', function($world) {
    $world->printDebug("`" . $world->command . "`  =>  " . $world->return);
});

$steps->Then('/^display last command output$/', function($world) {
    $world->printDebug("`" . $world->command . "`:\n" . $world->output);
});

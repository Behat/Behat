<?php

$steps->Given('/^a file named "([^"]*)" with:$/', function($world, $filename, $content) {
    $content = strtr($content, array("'''" => '"""'));

    file_put_contents($filename, $content);
});

$steps->When('/^I run "behat ([^"]*)"$/', function($world, $command) {
    $php     = 0 === mb_strpos(BEHAT_PHP_BIN_PATH, '/usr/bin/env')
             ? BEHAT_PHP_BIN_PATH
             : escapeshellarg(BEHAT_PHP_BIN_PATH);
    $command = strtr($command, array('\'' => '"'));

    exec($php . ' ' . escapeshellarg(BEHAT_BIN_PATH) . ' --no-time --no-colors ' . $command, $output, $return);

    $world->command = $command;
    $world->output  = trim(implode("\n", $output));
    $world->return  = $return;
});

$steps->Given('/^I am in the "([^"]*)" path$/', function($world, $path) {
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
        chdir($path);
    }
});

$steps->Then('/^file "([^"]*)" should exist$/', function($world, $path) {
    assertFileExists(getcwd() . DIRECTORY_SEPARATOR . $path);
});

$steps->Then('/^it should (fail|pass) with:$/', function($world, $success, $text) {
    if ('fail' === $success) {
        assertNotEquals(0, $world->return);
    } else {
        assertEquals(0, $world->return);
    }

    // windows path fix
    if ('/' !== DIRECTORY_SEPARATOR) {
        $text = preg_replace_callback('/ features\/[^\n ]+/', function($matches) {
            return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
        }, (string) $text);
        $text = preg_replace_callback('/\<span class\="path"\>features\/[^\<]+/', function($matches) {
            return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
        }, (string) $text);
        $text = preg_replace_callback('/\+[fd] [^ ]+/', function($matches) {
            return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
        }, (string) $text);
    }

    try {
        assertEquals((string) $text, $world->output);
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
    // windows path fix
    if ('/' !== DIRECTORY_SEPARATOR) {
        $text = preg_replace_callback('/ features\/[^\n ]+/', function($matches) {
            return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
        }, (string) $text);
        $text = preg_replace_callback('/\<span class\="path"\>features\/[^\<]+/', function($matches) {
            return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
        }, (string) $text);
        $text = preg_replace_callback('/\+[fd] [^ ]+/', function($matches) {
            return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
        }, (string) $text);
    }

    try {
        assertContains((string) $text, $world->output);
    } catch (Exception $e) {
        $diff = PHPUnit_Framework_TestFailure::exceptionToString($e);
        throw new Exception($diff, $e->getCode(), $e);
    }
});

$steps->Then('/^the output should not contain:$/', function($world, $text) {
    // windows path fix
    if ('/' !== DIRECTORY_SEPARATOR) {
        $text = preg_replace_callback('/ features\/[^\n ]+/', function($matches) {
            return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
        }, (string) $text);
    }

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

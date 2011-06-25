<?php

use Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\Pending;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Behat test suite context.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeatureContext extends BaseFeaturesContext
{
    /**
     * Last runned command name.
     *
     * @var     string
     */
    private $command;
    /**
     * Last runned command output.
     *
     * @var     string
     */
    private $output;
    /**
     * Last runned command return code.
     *
     * @var     integer
     */
    private $return;

    /**
     * Initializes context.
     *
     * @param   array   $parameters
     */
    public function __construct(array $parameters = array())
    {
        $this->useContext(new Hooks());
    }

    /**
     * {@inheritdoc}
     */
    public function aFileNamedWith($filename, PyStringNode $content)
    {
        $content = strtr((string) $content, array("'''" => '"""'));
        file_put_contents($filename, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function iAmInThePath($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
            chdir($path);
        }
    }

    /**
     * @Given /^file "([^"]*)" should exist$/
     *
     * Checks whether a file at provided path exists.
     *
     * @param   string  $path
     */
    public function fileShouldExist($path)
    {
        assertFileExists(getcwd() . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * @When /^I run "behat ([^"]*)"$/
     *
     * Runs behat command with provided parameters
     *
     * @param   string  $argumentsString
     */
    public function iRunBehat($argumentsString)
    {
        $php = 0 === mb_strpos(BEHAT_PHP_BIN_PATH, '/usr/bin/env')
             ? BEHAT_PHP_BIN_PATH
             : escapeshellarg(BEHAT_PHP_BIN_PATH);
        $argumentsString = strtr($argumentsString, array('\'' => '"'));

        exec($php . ' ' . escapeshellarg(BEHAT_BIN_PATH) . ' --no-time --no-colors ' . $argumentsString, $output, $return);

        $this->command = 'behat ' . $argumentsString;
        $this->output  = trim(implode("\n", $output));
        $this->return  = $return;
    }

    /**
     * @Then /^it should (fail|pass) with:$/
     *
     * Checks whether previously runned command passes|failes with provided output.
     *
     * @param   string                          $success    "fail" or "pass"
     * @param   Behat\Gherkin\Node\PyStringNode $text       PyString text instance
     */
    public function itShouldPassWith($success, PyStringNode $text)
    {
        if ('fail' === $success) {
            assertNotEquals(0, $this->return);
        } else {
            assertEquals(0, $this->return);
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
            assertEquals((string) $text, $this->output);
        } catch (Exception $e) {
            $diff = PHPUnit_Framework_TestFailure::exceptionToString($e);
            throw new Exception($diff, $e->getCode(), $e);
        }
    }

    /**
     * @Then /^display last command output$/
     *
     * Prints last command output string.
     */
    public function displayLastCommandOutput()
    {
        $this->printDebug("`" . $this->command . "`:\n" . $this->output);
    }

    /**
     * @Then /^the output should contain:$/
     *
     * Checks whether last command output contains provided string.
     *
     * @param   Behat\Gherkin\Node\PyStringNode $text   PyString text instance
     */
    public function theOutputShouldContain(PyStringNode $text)
    {
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
            assertContains((string) $text, $this->output);
        } catch (Exception $e) {
            $diff = PHPUnit_Framework_TestFailure::exceptionToString($e);
            throw new Exception($diff, $e->getCode(), $e);
        }
    }

    /**
     * @Then /^it should (fail|pass)$/
     *
     * Checks whether previously runned command failed|passed.
     *
     * @param   string  $success    "fail" or "pass"
     */
    public function itShouldFail($success)
    {
        if ('fail' === $success) {
            assertNotEquals(0, $this->return);
        } else {
            assertEquals(0, $this->return);
        }
    }
}

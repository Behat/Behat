<?php

use Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

// order of autoloading is undefined, so we should
// require parent class explicitly here
require_once 'BaseFeaturesContext.php';

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
     * Environment variable
     *
     * @var     string
     */
    private $env;
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
        $this->useContext('hooks', new Hooks());
        $this->useContext('support', new Support());
    }

    /**
     * {@inheritdoc}
     */
    public function aFileNamedWith($filename, PyStringNode $content)
    {
        $content = strtr((string) $content, array("'''" => '"""'));

        // call method of one of subcontexts
        $this->getSubcontext('support')->createFile($filename, $content);
    }

    /**
     * {@inheritdoc}
     */
    public function iAmInThePath($path)
    {
        // call method of one of subcontexts
        $this->getSubcontext('support')->moveToNewPath($path);
    }

    /**
     * Checks whether a file at provided path exists.
     *
     * @Given /^file "([^"]*)" should exist$/
     *
     * @param   string  $path
     */
    public function fileShouldExist($path)
    {
        assertFileExists(getcwd() . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * Sets specified ENV variable
     *
     * @When /^"BEHAT_PARAMS" environment variable is set to:$/
     *
     * @param   PyStringNode  $value
     */
    public function iSetEnvironmentVariable(PyStringNode $value)
    {
        $this->env = (string) $value;
    }

    /**
     * Runs behat command with provided parameters
     *
     * @When /^I run "behat(?: ([^"]*))?"$/
     *
     * @param   string  $argumentsString
     */
    public function iRunBehat($argumentsString = '')
    {
        $argumentsString = strtr($argumentsString, array('\'' => '"'));

        if ('/' === DIRECTORY_SEPARATOR) {
            $argumentsString .= ' 2>&1';
        }

        if ($this->env) {
            exec($command = sprintf('BEHAT_PARAMS="%s" %s %s %s',
                $this->env, BEHAT_PHP_BIN_PATH, escapeshellarg(BEHAT_BIN_PATH), $argumentsString
            ), $output, $return);
        } else {
            exec($command = sprintf('%s %s %s --no-time',
                BEHAT_PHP_BIN_PATH, escapeshellarg(BEHAT_BIN_PATH), $argumentsString
            ), $output, $return);
        }

        $this->command = 'behat ' . $argumentsString;
        $this->output  = trim(implode("\n", $output));
        $this->return  = $return;
    }

    /**
     * @When I escape ansi characters in the output
     */
    public function iEscapeAnsiCharactersInTheOutput()
    {
        $this->output = addcslashes($this->output, "\033");
    }

    /**
     * Checks whether previously runned command passes|failes with provided output.
     *
     * @Then /^it should (fail|pass) with:$/
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

        $text = strtr($text, array('\'\'\'' => '"""', '%PATH%' => realpath(getcwd())));

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
     * Checks whether specified file exists and contains specified string.
     *
     * @Given /^"([^"]*)" file should contain:$/
     *
     * @param   string                          $path   file path
     * @param   Behat\Gherkin\Node\PyStringNode $text   file content
     */
    public function fileShouldContain($path, PyStringNode $text)
    {
        try {
            assertFileExists($path);
            assertEquals((string) $text, trim(file_get_contents($path)));
        } catch (Exception $e) {
            $diff = PHPUnit_Framework_TestFailure::exceptionToString($e);
            throw new Exception($diff, $e->getCode(), $e);
        }
    }

    /**
     * Prints last command output string.
     *
     * @Then display last command output
     */
    public function displayLastCommandOutput()
    {
        $this->printDebug("`" . $this->command . "`:\n" . $this->output);
    }

    /**
     * Checks whether last command output contains provided string.
     *
     * @Then the output should contain:
     *
     * @param   Behat\Gherkin\Node\PyStringNode $text   PyString text instance
     */
    public function theOutputShouldContain(PyStringNode $text)
    {
        $text = strtr($text, array('\'\'\'' => '"""', '%PATH%' => realpath(getcwd())));

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
     * Checks whether previously runned command failed|passed.
     *
     * @Then /^it should (fail|pass)$/
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

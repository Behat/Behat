<?php

use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

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
        \PHPUnit_Framework_Assert::assertFileExists(getcwd() . DIRECTORY_SEPARATOR . $path);
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
            exec($command = sprintf('BEHAT_PARAMS=\'%s\' %s %s %s',
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
     * @param   string       $success    "fail" or "pass"
     * @param   PyStringNode $text       PyString text instance
     */
    public function itShouldPassWith($success, PyStringNode $text)
    {
        if ('fail' === $success) {
            \PHPUnit_Framework_Assert::assertNotEquals(0, $this->return);
        } else {
            \PHPUnit_Framework_Assert::assertEquals(0, $this->return);
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

        \PHPUnit_Framework_Assert::assertEquals((string) $text, $this->output);
    }

    /**
     * Checks whether specified file exists and contains specified string.
     *
     * @Given /^"([^"]*)" file should contain:$/
     *
     * @param   string       $path   file path
     * @param   PyStringNode $text   file content
     */
    public function fileShouldContain($path, PyStringNode $text)
    {
        \PHPUnit_Framework_Assert::assertFileExists($path);
        \PHPUnit_Framework_Assert::assertEquals((string) $text, trim(file_get_contents($path)));
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
     * @param   PyStringNode $text   PyString text instance
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

        \PHPUnit_Framework_Assert::assertContains((string) $text, $this->output);
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
            \PHPUnit_Framework_Assert::assertNotEquals(0, $this->return);
        } else {
            \PHPUnit_Framework_Assert::assertEquals(0, $this->return);
        }
    }
}

<?php

use Behat\Gherkin\Node\PyStringNode;

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

        $langReset = '';

        // Don't reset the LANG variable on HHVM, because it breaks HHVM itself
        if (!defined('HHVM_VERSION')) {
            $langReset = 'LANG=en '; // Ensures that the default language is en, whatever the OS locale is.
        }

        if ($this->env) {
            exec($command = sprintf('%sBEHAT_PARAMS=\'%s\' %s %s %s',
                $langReset, $this->env, BEHAT_PHP_BIN_PATH, escapeshellarg(BEHAT_BIN_PATH), $argumentsString
            ), $output, $return);
        } else {
            exec($command = sprintf('%s%s %s %s --no-time',
                $langReset, BEHAT_PHP_BIN_PATH, escapeshellarg(BEHAT_BIN_PATH), $argumentsString
            ), $output, $return);
        }

        $this->command = 'behat ' . $argumentsString;
        $this->output  = trim(implode("\n", $output));
        $this->return  = $return;

        // Replace wrong warning message of HHVM
        $this->output = str_replace('Notice: Undefined index: ', 'Notice: Undefined offset: ', $this->output);
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
        $this->itShouldFail($success);
        $this->theOutputShouldContain($text);
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

        if ('' === $text) {
            \PHPUnit_Framework_Assert::assertEquals($text, $this->output);

            return;
        }

        // Normalize the way to reset Console styles because of changes in Symfony 2.5
        $output = str_replace(array('[39m', '[39;49m'), '[0m', $this->output);
        $output = preg_replace('/\[39;2(?:2|4|5|7|8)m/', '[0m', $output);

        \PHPUnit_Framework_Assert::assertContains((string) $text, $output);
    }

    /**
     * @Then /^the junit file "([^"]*)" should contain:$/
     */
    public function theJunitFileShouldContain($file, PyStringNode $text)
    {
        PHPUnit_Framework_Assert::assertFileExists($file);

        // replace random time ...
        $contents = preg_replace('@time="[0-9.]*"@', 'time="XXX"', file_get_contents($file));

        // replace random path
        $contents = preg_replace('@[0-9a-zA-Z]{32}@', 'XXX', $contents);

        // fix random path in exception ...
        $contents = preg_replace('@<!\[CDATA\[.*\]\]>@s', '<![CDATA[XXX]]>', $contents);

        PHPUnit_Framework_Assert::assertEquals($contents, (string)$text);
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

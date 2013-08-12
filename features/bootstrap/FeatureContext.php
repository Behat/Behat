<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\ContextInterface;
use Behat\Gherkin\Node\PyStringNode;

/**
 * Behat test suite context.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeatureContext implements ContextInterface
{
    /**
     * Environment variable
     *
     * @var string
     */
    private $env;
    /**
     * Last runned command name.
     *
     * @var string
     */
    private $command;
    /**
     * Last runned command output.
     *
     * @var string
     */
    private $output;
    /**
     * Last runned command return code.
     *
     * @var integer
     */
    private $return;

    /**
     * Cleans test folders in the temporary directory.
     *
     * @BeforeSuite
     */
    public static function cleanTestFolders()
    {
        if (is_dir($dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat')) {
            self::clearDirectory($dir);
        }
    }

    /**
     * Prepares test folders in the temporary directory.
     *
     * @BeforeScenario
     */
    public function prepareTestFolders($event)
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat' . DIRECTORY_SEPARATOR .
            md5(microtime() * rand(0, 10000));

        mkdir($dir, 0777, true);
        chdir($dir);

        mkdir('features');
        mkdir('features' . DIRECTORY_SEPARATOR . 'bootstrap');
        mkdir('features' . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'i18n');

        mkdir('features' . DIRECTORY_SEPARATOR . 'support');
        mkdir('features' . DIRECTORY_SEPARATOR . 'steps');
        mkdir('features' . DIRECTORY_SEPARATOR . 'steps' . DIRECTORY_SEPARATOR . 'i18n');
    }

    /**
     * Creates a file with specified name and context in current workdir.
     *
     * @Given /^(?:there is )?a file named "([^"]*)" with:$/
     *
     * @param   string       $filename   name of the file (relative path)
     * @param   PyStringNode $content    PyString string instance
     */
    public function aFileNamedWith($filename, PyStringNode $content)
    {
        $content = strtr((string)$content, array("'''" => '"""'));
        $this->createFile($filename, $content);
    }

    /**
     * Moves user to the specified path.
     *
     * @Given /^I am in the "([^"]*)" path$/
     *
     * @param   string $path
     */
    public function iAmInThePath($path)
    {
        $this->moveToNewPath($path);
    }

    /**
     * Checks whether a file at provided path exists.
     *
     * @Given /^file "([^"]*)" should exist$/
     *
     * @param   string $path
     */
    public function fileShouldExist($path)
    {
        PHPUnit_Framework_Assert::assertFileExists(getcwd() . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * Sets specified ENV variable
     *
     * @When /^"BEHAT_PARAMS" environment variable is set to:$/
     *
     * @param   PyStringNode $value
     */
    public function iSetEnvironmentVariable(PyStringNode $value)
    {
        $this->env = (string)$value;
    }

    /**
     * Runs behat command with provided parameters
     *
     * @When /^I run "behat(?: ([^"]*))?"$/
     *
     * @param   string $argumentsString
     */
    public function iRunBehat($argumentsString = '')
    {
        $argumentsString = strtr($argumentsString, array('\'' => '"'));

        if ('/' === DIRECTORY_SEPARATOR) {
            $argumentsString .= ' 2>&1';
        }

        if ($this->env) {
            exec(sprintf('BEHAT_PARAMS=\'%s\' %s %s %s',
                $this->env, BEHAT_PHP_BIN_PATH, escapeshellarg(BEHAT_BIN_PATH), $argumentsString
            ), $output, $return);
        } else {
            exec(sprintf('%s %s %s --no-time',
                BEHAT_PHP_BIN_PATH, escapeshellarg(BEHAT_BIN_PATH), $argumentsString
            ), $output, $return);
        }

        $this->command = 'behat ' . $argumentsString;
        $this->output = trim(implode("\n", $output));
        $this->return = $return;
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
            PHPUnit_Framework_Assert::assertNotEquals(0, $this->return);
        } else {
            PHPUnit_Framework_Assert::assertEquals(0, $this->return);
        }

        $text = strtr($text, array('\'\'\'' => '"""', '%PATH%' => realpath(getcwd())));

        // windows path fix
        if ('/' !== DIRECTORY_SEPARATOR) {
            $text = preg_replace_callback('/ features\/[^\n ]+/', function ($matches) {
                return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
            }, (string)$text);
            $text = preg_replace_callback('/\<span class\="path"\>features\/[^\<]+/', function ($matches) {
                return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
            }, (string)$text);
            $text = preg_replace_callback('/\+[fd] [^ ]+/', function ($matches) {
                return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
            }, (string)$text);
        }

        PHPUnit_Framework_Assert::assertEquals((string)$text, $this->output);
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
        PHPUnit_Framework_Assert::assertFileExists($path);
        PHPUnit_Framework_Assert::assertEquals((string)$text, trim(file_get_contents($path)));
    }

    /**
     * Prints last command output string.
     *
     * @Then display output
     */
    public function displayLastCommandOutput()
    {
        print("\n\n`" . $this->command . "`:\n" . $this->output . "\n\n");
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
            $text = preg_replace_callback('/ features\/[^\n ]+/', function ($matches) {
                return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
            }, (string)$text);
            $text = preg_replace_callback('/\<span class\="path"\>features\/[^\<]+/', function ($matches) {
                return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
            }, (string)$text);
            $text = preg_replace_callback('/\+[fd] [^ ]+/', function ($matches) {
                return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
            }, (string)$text);
        }

        PHPUnit_Framework_Assert::assertContains((string)$text, $this->output);
    }

    /**
     * Checks whether previously runned command failed|passed.
     *
     * @Then /^it should (fail|pass)$/
     *
     * @param   string $success    "fail" or "pass"
     */
    public function itShouldFail($success)
    {
        if ('fail' === $success) {
            PHPUnit_Framework_Assert::assertNotEquals(0, $this->return);
        } else {
            PHPUnit_Framework_Assert::assertEquals(0, $this->return);
        }
    }

    private function createFile($filename, $content)
    {
        file_put_contents($filename, $content);
    }

    private function moveToNewPath($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        chdir($path);
    }

    /**
     * Removes files and folders recursively at provided path.
     *
     * @param string $path
     */
    private static function clearDirectory($path)
    {
        $files = scandir($path);
        array_shift($files);
        array_shift($files);

        foreach ($files as $file) {
            $file = $path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($file)) {
                self::clearDirectory($file);
            } else {
                unlink($file);
            }
        }

        rmdir($path);
    }
}

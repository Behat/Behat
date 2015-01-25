<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Behat test suite context.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeatureContext implements Context
{
    /**
     * @var string
     */
    private $phpBin;
    /**
     * @var Process
     */
    private $process;
    /**
     * @var string
     */
    private $workingDir;

    /**
     * Cleans test folders in the temporary directory.
     *
     * @BeforeSuite
     * @AfterSuite
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
    public function prepareTestFolders()
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat' . DIRECTORY_SEPARATOR .
            md5(microtime() * rand(0, 10000));

        mkdir($dir . '/features/bootstrap/i18n', 0777, true);

        $phpFinder = new PhpExecutableFinder();
        if (false === $php = $phpFinder->find()) {
            throw new \RuntimeException('Unable to find the PHP executable.');
        }
        $this->workingDir = $dir;
        $this->phpBin = $php;
        $this->process = new Process(null);
    }

    /**
     * Creates a file with specified name and context in current workdir.
     *
     * @Given /^(?:there is )?a file named "([^"]*)" with:$/
     *
     * @param   string       $filename name of the file (relative path)
     * @param   PyStringNode $content  PyString string instance
     */
    public function aFileNamedWith($filename, PyStringNode $content)
    {
        $content = strtr((string) $content, array("'''" => '"""'));
        $this->createFile($this->workingDir . '/' . $filename, $content);
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
        PHPUnit_Framework_Assert::assertFileExists($this->workingDir . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * Sets specified ENV variable
     *
     * @When /^"BEHAT_PARAMS" environment variable is set to:$/
     *
     * @param PyStringNode $value
     */
    public function iSetEnvironmentVariable(PyStringNode $value)
    {
        $this->process->setEnv(array('BEHAT_PARAMS' => (string) $value));
    }

    /**
     * Runs behat command with provided parameters
     *
     * @When /^I run "behat(?: ((?:\"|[^"])*))?"$/
     *
     * @param   string $argumentsString
     */
    public function iRunBehat($argumentsString = '')
    {
        $argumentsString = strtr($argumentsString, array('\'' => '"'));

        $this->process->setWorkingDirectory($this->workingDir);
        $this->process->setCommandLine(
            sprintf(
                '%s %s %s %s',
                $this->phpBin,
                escapeshellarg(BEHAT_BIN_PATH),
                $argumentsString,
                strtr('--format-settings=\'{"timer": false}\'', array('\'' => '"', '"' => '\"'))
            )
        );

        // Don't reset the LANG variable on HHVM, because it breaks HHVM itself
        if (!defined('HHVM_VERSION')) {
            $env = $this->process->getEnv();
            $env['LANG'] = 'en'; // Ensures that the default language is en, whatever the OS locale is.
            $this->process->setEnv($env);
        }

        $this->process->start();
        $this->process->wait();
    }

    /**
     * Checks whether previously ran command passes|fails with provided output.
     *
     * @Then /^it should (fail|pass) with:$/
     *
     * @param   string       $success "fail" or "pass"
     * @param   PyStringNode $text    PyString text instance
     */
    public function itShouldPassWith($success, PyStringNode $text)
    {
        $this->itShouldFail($success);
        $this->theOutputShouldContain($text);
    }

    /**
     * Checks whether specified file exists and contains specified string.
     *
     * @Then /^"([^"]*)" file should contain:$/
     *
     * @param   string       $path file path
     * @param   PyStringNode $text file content
     */
    public function fileShouldContain($path, PyStringNode $text)
    {
        $path = $this->workingDir . '/' . $path;
        PHPUnit_Framework_Assert::assertFileExists($path);

        $fileContent = trim(file_get_contents($path));
        // Normalize the line endings in the output
        if ("\n" !== PHP_EOL) {
            $fileContent = str_replace(PHP_EOL, "\n", $fileContent);
        }

        PHPUnit_Framework_Assert::assertEquals($this->getExpectedOutput($text), $fileContent);
    }

    /**
     * Checks whether last command output contains provided string.
     *
     * @Then the output should contain:
     *
     * @param   PyStringNode $text PyString text instance
     */
    public function theOutputShouldContain(PyStringNode $text)
    {
        PHPUnit_Framework_Assert::assertContains($this->getExpectedOutput($text), $this->getOutput());
    }

    private function getExpectedOutput(PyStringNode $expectedText)
    {
        $text = strtr($expectedText, array('\'\'\'' => '"""', '%%TMP_DIR%%' => sys_get_temp_dir() . DIRECTORY_SEPARATOR));

        // windows path fix
        if ('/' !== DIRECTORY_SEPARATOR) {
            $text = preg_replace_callback(
                '/ features\/[^\n ]+/', function ($matches) {
                    return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
                }, $text
            );
            $text = preg_replace_callback(
                '/\<span class\="path"\>features\/[^\<]+/', function ($matches) {
                    return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
                }, $text
            );
            $text = preg_replace_callback(
                '/\+[fd] [^ ]+/', function ($matches) {
                    return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
                }, $text
            );
        }

        return $text;
    }

    /**
     * Checks whether previously ran command failed|passed.
     *
     * @Then /^it should (fail|pass)$/
     *
     * @param   string $success "fail" or "pass"
     */
    public function itShouldFail($success)
    {
        if ('fail' === $success) {
            if (0 === $this->getExitCode()) {
                echo 'Actual output:' . PHP_EOL . PHP_EOL . $this->getOutput();
            }

            PHPUnit_Framework_Assert::assertNotEquals(0, $this->getExitCode());
        } else {
            if (0 !== $this->getExitCode()) {
                echo 'Actual output:' . PHP_EOL . PHP_EOL . $this->getOutput();
            }

            PHPUnit_Framework_Assert::assertEquals(0, $this->getExitCode());
        }
    }

    private function getExitCode()
    {
        return $this->process->getExitCode();
    }

    private function getOutput()
    {
        $output = $this->process->getErrorOutput() . $this->process->getOutput();

        // Normalize the line endings in the output
        if ("\n" !== PHP_EOL) {
            $output = str_replace(PHP_EOL, "\n", $output);
        }

        // Replace wrong warning message of HHVM
        $output = str_replace('Notice: Undefined index: ', 'Notice: Undefined offset: ', $output);

        return trim(preg_replace("/ +$/m", '', $output));
    }

    private function createFile($filename, $content)
    {
        $path = dirname($filename);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        file_put_contents($filename, $content);
    }

    private function moveToNewPath($path)
    {
        $newWorkingDir = $this->workingDir .'/' . $path;
        if (!file_exists($newWorkingDir)) {
            mkdir($newWorkingDir, 0777, true);
        }

        $this->workingDir = $newWorkingDir;
    }

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

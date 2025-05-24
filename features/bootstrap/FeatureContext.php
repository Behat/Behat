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
use Behat\Gherkin\Node\TableNode;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use PHPUnit\Framework\Assert;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\DiffOnlyOutputBuilder;
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
     * @var string
     */
    private $tempDir;
    /**
     * @var string
     */
    private $options = '--format-settings=\'{"timer": false}\' --no-interaction';
    /**
     * @var array
     */
    private $env = [];
    /**
     * @var string
     */
    private $answerString;

    private bool $workingDirChanged = false;

    /**
     * Cleans test folders in the temporary directory.
     *
     * @BeforeSuite
     *
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
            md5(microtime() . rand(0, 10000));

        mkdir($dir . '/features/bootstrap/i18n', 0777, true);

        $phpFinder = new PhpExecutableFinder();
        if (false === $php = $phpFinder->find()) {
            throw new RuntimeException('Unable to find the PHP executable.');
        }
        $this->workingDir = $dir;
        $this->tempDir = $dir;
        $this->phpBin = $php;
    }

    /**
     * Creates a file with specified name and context in current workdir.
     *
     * @Given /^(?:there is )?a file named "([^"]*)" with:$/
     *
     * @param string       $filename name of the file (relative path)
     * @param PyStringNode $content  PyString string instance
     */
    public function aFileNamedWith($filename, PyStringNode $content)
    {
        $content = strtr((string) $content, ["'''" => '"""']);
        $this->createFile($this->workingDir . '/' . $filename, $content);
    }

    /**
     * Creates a empty file with specified name in current workdir.
     *
     * @Given /^(?:there is )?a file named "([^"]*)"$/
     *
     * @param string $filename name of the file (relative path)
     */
    public function aFileNamed($filename)
    {
        $this->createFile($this->workingDir . '/' . $filename, '');
    }

    /**
     * Creates a noop feature context in current workdir.
     *
     * @Given /^(?:there is )?a some feature context$/
     */
    public function aNoopFeatureContext()
    {
        $filename = 'features/bootstrap/FeatureContext.php';
        $content = <<<'EOL'
<?php

use Behat\Behat\Context\Context;

class FeatureContext implements Context
{
}
EOL;
        $this->createFile($this->workingDir . '/' . $filename, $content);
    }

    /**
     * Creates a noop feature in current workdir.
     *
     * @Given /^(?:there is )?a some feature scenarios/
     */
    public function aNoopFeature()
    {
        $filename = 'features/bootstrap/FeatureContext.php';
        $content = <<<'EOL'
Feature:
        Scenario:
          When this scenario executes
EOL;
        $this->createFile($this->workingDir . '/' . $filename, $content);
    }

    /**
     * Moves user to the specified path.
     *
     * @Given /^I am in the "([^"]*)" path$/
     *
     * @param string $path
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
        Assert::assertFileExists($this->workingDir . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * Sets specified ENV variable.
     *
     * @When /^the "([^"]*)" environment variable is set to "([^"]*)"$/
     */
    public function iSetEnvironmentVariable($name, $value)
    {
        $this->env = [$name => (string) $value];
    }

    /**
     * Sets the BEHAT_PARAMS env variable.
     *
     * @When /^"BEHAT_PARAMS" environment variable is set to:$/
     */
    public function iSetBehatParamsEnvironmentVariable(PyStringNode $value)
    {
        $this->env = ['BEHAT_PARAMS' => (string) $value];
    }

    /**
     * @When I set the working directory to the :dir fixtures folder
     */
    public function iSetTheWorkingDirectoryToTheFixturesFolder($dir): void
    {
        $basePath = dirname(__DIR__, 2) . '/tests/Fixtures/';
        $dir = $basePath . $dir;
        if (!is_dir($dir)) {
            throw new RuntimeException(sprintf('The directory "%s" does not exist', $dir));
        }
        $this->workingDir = $dir;
        $this->workingDirChanged = true;
    }

    #[Given('I clear the default behat options')]
    public function iClearTheDefaultBehatOptions(): void
    {
        $this->options = '';
    }

    #[Given('I provide the following options for all behat invocations:')]
    public function iProvideTheFollowingOptionsForAllBehatInvocations(TableNode $table): void
    {
        $this->addBehatOptions($table);
    }

    #[When('I run behat with the following additional options:')]
    public function iRunBehatWithTheFollowingAdditionalOptions(TableNode $table): void
    {
        $this->addBehatOptions($table);
        $this->iRunBehat();
    }

    /**
     * Runs behat command with provided parameters.
     *
     * @When /^I run "behat(?: ((?:\"|[^"])*))?"$/
     *
     * @param string $argumentsString
     */
    public function iRunBehat($argumentsString = '')
    {
        $argumentsString = strtr($argumentsString, ['\'' => '"']);

        $cmd = sprintf(
            '%s %s %s %s',
            $this->phpBin,
            escapeshellarg(BEHAT_BIN_PATH),
            $argumentsString,
            strtr($this->options, ['\'' => '"', '"' => '\"'])
        );

        $this->process = Process::fromShellCommandline($cmd);

        // Prepare the process parameters.
        $this->process->setTimeout(20);
        $this->process->setEnv($this->env);
        $this->process->setWorkingDirectory($this->workingDir);

        if (!empty($this->answerString)) {
            $this->process->setInput($this->answerString);
        }

        // Don't reset the LANG variable on HHVM, because it breaks HHVM itself
        if (!defined('HHVM_VERSION')) {
            $env = $this->process->getEnv();
            $env['LANG'] = 'en'; // Ensures that the default language is en, whatever the OS locale is.
            $this->process->setEnv($env);
        }

        $this->process->run();
    }

    /**
     * Runs behat command with provided parameters in interactive mode.
     *
     * @When /^I answer "([^"]+)" when running "behat(?: ((?:\"|[^"])*))?"$/
     *
     * @param string $answerString
     * @param string $argumentsString
     */
    public function iRunBehatInteractively($answerString, $argumentsString)
    {
        $this->env['SHELL_INTERACTIVE'] = true;

        $this->answerString = $answerString;

        $this->options = '--format-settings=\'{"timer": false}\'';
        $this->iRunBehat($argumentsString);
    }

    /**
     * Runs behat command in debug mode.
     *
     * @When /^I run behat in debug mode$/
     */
    public function iRunBehatInDebugMode()
    {
        $this->options = '';
        $this->iRunBehat('--debug');
    }

    /**
     * Checks whether previously ran command passes|fails with provided output.
     *
     * @Then /^it should (fail|pass) with:$/
     *
     * @param 'pass'|'fail' $success
     */
    public function itShouldPassOrFailWith($success, PyStringNode $text)
    {
        $isCorrect = $this->exitCodeIsCorrect($success);

        $outputMessage = [];
        $hasError = false;

        if (!$isCorrect) {
            $hasError = true;
            $outputMessage[] = 'Expected previous command to ' . strtoupper($success) . ' but got exit code ' . $this->getExitCode();
        } else {
            $outputMessage[] = 'Command did ' . strtoupper($success) . ' as expected.';
        }

        if (!str_contains($this->getOutput(), $this->getExpectedOutput($text))) {
            $hasError = true;
            $outputMessage[] = $this->getOutputDiff($text);
        } else {
            $outputMessage[] = 'Output is as expected.';
        }

        if ($hasError) {
            throw new UnexpectedValueException(
                implode(PHP_EOL . PHP_EOL, $outputMessage)
            );
        }
    }

    /**
     * Checks whether previously runned command passes|failes with no output.
     *
     * @Then /^it should (fail|pass) with no output$/
     *
     * @param 'pass'|'fail' $success
     */
    public function itShouldPassOrFailWithNoOutput($success)
    {
        Assert::assertEmpty($this->getOutput());
        $this->itShouldPassOrFail($success);
    }

    /**
     * Checks whether specified file exists and contains specified string.
     *
     * @Then /^"([^"]*)" file should contain:$/
     *
     * @param string       $path file path
     * @param PyStringNode $text file content
     */
    public function fileShouldContain($path, PyStringNode $text)
    {
        $path = $this->workingDir . '/' . $path;
        Assert::assertFileExists($path);

        $fileContent = trim(file_get_contents($path));
        // Normalize the line endings in the output
        if ("\n" !== PHP_EOL) {
            $fileContent = str_replace(PHP_EOL, "\n", $fileContent);
        }

        Assert::assertEquals($this->getExpectedOutput($text), $fileContent);
    }

    /**
     * Checks whether specified content and structure of the xml is correct without worrying about layout.
     *
     * @Then /^(?:the\s)?"([^"]*)" file xml should be like:$/
     *
     * @param string       $path file path
     * @param PyStringNode $text file content
     */
    public function fileXmlShouldBeLike($path, PyStringNode $text)
    {
        $path = $this->workingDir . '/' . $path;
        $this->checkXmlFileContents($path, $text);
    }

    #[When('I copy the :file file to the temp folder')]
    public function iCopyTheFileToTheTempFolder($file): void
    {
        $origin = $this->workingDir . '/' . $file;
        $destination = $this->tempDir . '/' . $file;
        copy($origin, $destination);
    }

    #[Then('the temp :file file should be like:')]
    public function theTempFileShouldBeLike($file, PyStringNode $string): void
    {
        $path = $this->tempDir . '/' . $file;
        Assert::assertFileExists($path);

        $fileContent = trim(file_get_contents($path));

        Assert::assertSame($string->getRaw(), $fileContent);
    }

    #[Then('the temp :file file should have been removed')]
    public function theTempFileShouldHaveBeenRemoved($file): void
    {
        $path = $this->tempDir . '/' . $file;
        Assert::assertFileDoesNotExist($path);
    }

    /**
     * @Then the temp :path file xml should be like:
     */
    public function theTempFileFileXmlShouldBeLike($path, PyStringNode $text): void
    {
        $path = $this->tempDir . '/' . $path;
        $this->checkXmlFileContents($path, $text);
    }

    private function checkXmlFileContents($path, PyStringNode $text)
    {
        Assert::assertFileExists($path);

        $fileContent = trim(file_get_contents($path));

        $fileContent = preg_replace('/time="\d\.\d{3}"/U', 'time="-IGNORE-VALUE-"', $fileContent);

        // The placeholder is necessary because of different separators on Unix and Windows environments
        $text = str_replace('-DIRECTORY-SEPARATOR-', DIRECTORY_SEPARATOR, $text);

        $dom = new DOMDocument();
        $dom->loadXML($text);
        $dom->formatOutput = true;

        Assert::assertEquals(trim($dom->saveXML(null, LIBXML_NOEMPTYTAG)), $fileContent);
    }

    /**
     * Checks whether last command output contains provided string.
     *
     * @Then the output should contain:
     *
     * @param PyStringNode $text PyString text instance
     */
    public function theOutputShouldContain(PyStringNode $text)
    {
        if (str_contains($this->getOutput(), $this->getExpectedOutput($text))) {
            return;
        }

        throw new UnexpectedValueException(
            $this->getOutputDiff($text)
        );
    }

    private function getExpectedOutput(PyStringNode $expectedText)
    {
        $text = strtr($expectedText, [
            '\'\'\'' => '"""',
            '%%TMP_DIR%%' => sys_get_temp_dir() . DIRECTORY_SEPARATOR,
            '%%WORKING_DIR%%' => realpath($this->workingDir . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR,
            '%%DS%%' => DIRECTORY_SEPARATOR,
        ]);

        // windows path fix
        if ('/' !== DIRECTORY_SEPARATOR) {
            $text = preg_replace_callback(
                '/[ "](features|tests)\/[^\n "]+/',
                function ($matches) {
                    return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
                },
                $text
            );
            $text = preg_replace_callback(
                '/\<span class\="path"\>features\/[^\<]+/',
                function ($matches) {
                    return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
                },
                $text
            );
            $text = preg_replace_callback(
                '/\+[fd] [^ ]+/',
                function ($matches) {
                    return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
                },
                $text
            );

            // error stacktrace
            $text = preg_replace_callback(
                '/#\d+ [^:]+:/',
                function ($matches) {
                    return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
                },
                $text
            );

            // texts with absolute paths
            $text = preg_replace_callback(
                '/\{BASE_PATH\}[^\n \<"]+/',
                function ($matches) {
                    return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
                },
                $text
            );

            // texts in editor URLs
            $text = preg_replace_callback(
                '/open\?file[^\<"]+/',
                function ($matches) {
                    return str_replace('/', DIRECTORY_SEPARATOR, $matches[0]);
                },
                $text
            );
        }

        return $text;
    }

    /**
     * Checks whether previously ran command failed|passed.
     *
     * @Then /^it should (fail|pass)$/
     *
     * @param 'pass'|'fail' $success
     */
    public function itShouldPassOrFail($success)
    {
        $isCorrect = $this->exitCodeIsCorrect($success);

        if ($isCorrect) {
            return;
        }

        throw new UnexpectedValueException(
            'Expected previous command to ' . strtoupper($success) . ' but got exit code ' . $this->getExitCode()
        );
    }

    /**
     * Checks whether the file is valid according to an XML schema.
     *
     * @Then /^the file "([^"]+)" should be a valid document according to "([^"]+)"$/
     *
     * @param string $xmlFile
     * @param string $schemaPath relative to features/bootstrap/schema
     */
    public function xmlShouldBeValid($xmlFile, $schemaPath)
    {
        $path = $this->workingDir . '/' . $xmlFile;
        $this->checkXmlIsValid($path, $schemaPath);
    }

    /**
     * @Then the temp file :xmlFile should be a valid document according to :schemaPath
     */
    public function theTempFileShouldBeAValidDocumentAccordingTo($xmlFile, $schemaPath): void
    {
        $path = $this->tempDir . '/' . $xmlFile;
        $this->checkXmlIsValid($path, $schemaPath);
    }

    private function checkXmlIsValid(string $xmlFile, string $schemaPath): void
    {
        $dom = new DOMDocument();
        $dom->load($xmlFile);

        $dom->schemaValidate(__DIR__ . '/schema/' . $schemaPath);
    }

    private function getExitCode()
    {
        return $this->process->getExitCode();
    }

    private function getOutput(): string
    {
        $output = $this->process->getErrorOutput() . $this->process->getOutput();

        // Normalize the line endings and directory separators in the output
        if ("\n" !== PHP_EOL) {
            $output = str_replace(PHP_EOL, "\n", $output);
        }

        // Remove location of the project
        $output = str_replace(
            realpath(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR,
            '{BASE_PATH}',
            $output
        );

        // Replace wrong warning message of HHVM
        $output = str_replace('Notice: Undefined index: ', 'Notice: Undefined offset: ', $output);

        // replace error messages that changed in PHP8
        $output = str_replace('Warning: Undefined array key', 'Notice: Undefined offset:', $output);
        $output = preg_replace('/Class "([^"]+)" not found/', 'Class \'$1\' not found', $output);

        return trim(preg_replace('/ +$/m', '', $output));
    }

    private function createFile($filename, $content)
    {
        if ($this->workingDirChanged) {
            throw new RuntimeException('Trying to write a file in a fixtures folder');
        }
        $path = dirname($filename);
        $this->createDirectory($path);

        file_put_contents($filename, $content);
    }

    private function createDirectory($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }

    private function moveToNewPath($path)
    {
        $newWorkingDir = $this->workingDir . '/' . $path;
        if (!file_exists($newWorkingDir)) {
            mkdir($newWorkingDir, 0777, true);
        }

        $this->workingDir = $newWorkingDir;
    }

    /**
     * @param 'fail'|'pass' $success
     */
    private function exitCodeIsCorrect(string $success): bool
    {
        return match ($success) {
            'fail' => 0 !== $this->getExitCode(),
            'pass' => 0 === $this->getExitCode(),
        };
    }

    private function getOutputDiff(PyStringNode $expectedText): string
    {
        $differ = new Differ(new DiffOnlyOutputBuilder());

        return $differ->diff($this->getExpectedOutput($expectedText), $this->getOutput());
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
    }

    private function addBehatOptions(TableNode $table): void
    {
        $rows = $table->getHash();
        foreach ($rows as $row) {
            $option = $row['option'];
            $value = $row['value'];
            if ($value !== '') {
                if (str_starts_with($value, '{SYSTEM_TMP_DIR}')) {
                    $value = $this->tempDir . substr($value, strlen('{SYSTEM_TMP_DIR}'));
                }
                if (str_starts_with($value, '{BASE_PATH}')) {
                    $basePath = realpath(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR;
                    $value = $basePath . substr($value, strlen('{BASE_PATH}'));
                }

                if ($option === '--remove-prefix' && DIRECTORY_SEPARATOR !== '/') {
                    $value = str_replace('/', DIRECTORY_SEPARATOR, $value);
                }
                $option .= '=' . $value;
            }
            $this->options .= ' ' . $option;
        }
    }
}

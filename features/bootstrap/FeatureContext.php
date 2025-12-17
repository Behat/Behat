<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Context;
use Behat\Behat\Output\Printer\Formatter\ConsoleFormatter;
use Behat\Behat\Util\StrictRegex;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Hook\AfterSuite;
use Behat\Hook\BeforeScenario;
use Behat\Hook\BeforeSuite;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\Assert;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\DiffOnlyOutputBuilder;
use Symfony\Component\Filesystem\Filesystem;
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
    private $options = '--format-settings=\'{"timer": false}\' --no-interaction';
    /**
     * @var array
     */
    private $env = [];
    /**
     * @var string
     */
    private $answerString;

    private ?int $errorLevel = null;

    public function __construct(
        private readonly Filesystem $filesystem = new Filesystem(),
    ) {
    }

    /**
     * Cleans test folders in the temporary directory.
     */
    #[BeforeSuite]
    #[AfterSuite]
    public static function cleanTestFolders(): void
    {
        (new Filesystem())->remove(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat');
    }

    /**
     * Prepares test folders in the temporary directory.
     */
    #[BeforeScenario]
    public function prepareTestFolders(): void
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'behat' . DIRECTORY_SEPARATOR .
            md5(microtime() . random_int(0, 10000));

        $this->filesystem->mkdir($dir);

        $phpFinder = new PhpExecutableFinder();
        if (false === $php = $phpFinder->find()) {
            throw new RuntimeException('Unable to find the PHP executable.');
        }
        $this->workingDir = $dir;
        $this->phpBin = $php;
    }

    /**
     * Creates a file with specified name and context in current workdir.
     *
     * @param string       $filename name of the file (relative path)
     * @param PyStringNode $content  PyString string instance
     */
    #[Given('/^(?:there is )?a file named "([^"]*)" with:$/')]
    public function aFileNamedWith($filename, PyStringNode $content): void
    {
        $content = strtr((string) $content, ["'''" => '"""']);
        $this->createFileInWorkingDir($filename, $content);
    }

    /**
     * Creates an empty file with specified name in current workdir.
     *
     * @param string $filename name of the file (relative path)
     */
    #[Given('/^(?:there is )?a file named "([^"]*)"$/')]
    public function aFileNamed($filename): void
    {
        $this->createFileInWorkingDir($filename, '');
    }

    /**
     * Copies a file from source to destination in current workdir.
     *
     * @param string $source source file path (relative to workdir)
     * @param string $destination destination file path (relative to workdir)
     */
    #[Given('/^I copy "([^"]*)" to "([^"]*)"$/')]
    public function iCopyFileTo($source, $destination): void
    {
        $sourcePath = $this->workingDir . '/' . $source;
        $destinationPath = $this->workingDir . '/' . $destination;

        if (!file_exists($sourcePath)) {
            throw new RuntimeException(sprintf('Source file "%s" does not exist (full path: %s)', $source, $sourcePath));
        }

        $this->filesystem->copy($sourcePath, $destinationPath, true);

        // Update timestamp to bypass file cache
        touch($destinationPath);
    }

    /**
     * Creates a noop feature context in current workdir.
     */
    #[Given('/^(?:there is )?a some feature context$/')]
    public function aNoopFeatureContext(): void
    {
        $filename = 'features/bootstrap/FeatureContext.php';
        $content = <<<'EOL'
<?php

use Behat\Behat\Context\Context;

class FeatureContext implements Context
{
}
EOL;
        $this->createFileInWorkingDir($filename, $content);
    }

    /**
     * Creates a noop feature in current workdir.
     */
    #[Given('/^(?:there is )?a some feature scenarios/')]
    public function aNoopFeature(): void
    {
        $filename = 'features/bootstrap/FeatureContext.php';
        $content = <<<'EOL'
Feature:
        Scenario:
          When this scenario executes
EOL;
        $this->createFileInWorkingDir($filename, $content);
    }

    /**
     * Moves user to the specified path.
     *
     * @param string $path
     */
    #[Given('/^I am in the "([^"]*)" path$/')]
    public function iAmInThePath($path): void
    {
        $this->moveToNewPath($path);
    }

    /**
     * Checks whether a file at provided path exists.
     *
     * @param   string $path
     */
    #[Given('/^file "([^"]*)" should exist$/')]
    public function fileShouldExist($path): void
    {
        Assert::assertFileExists($this->workingDir . DIRECTORY_SEPARATOR . $path);
    }

    /**
     * Sets specified ENV variable.
     */
    #[When('/^the "([^"]*)" environment variable is set to "([^"]*)"$/')]
    public function iSetEnvironmentVariable($name, $value): void
    {
        $this->env = [$name => (string) $value];
    }

    /**
     * Sets the BEHAT_PARAMS env variable.
     */
    #[When('/^"BEHAT_PARAMS" environment variable is set to:$/')]
    public function iSetBehatParamsEnvironmentVariable(PyStringNode $value): void
    {
        $this->env = ['BEHAT_PARAMS' => (string) $value];
    }

    #[When('I initialise the working directory from the :dir fixtures folder')]
    public function iSetTheWorkingDirectoryToTheFixturesFolder($dir): void
    {
        $basePath = dirname(__DIR__, 2) . '/tests/Fixtures/';
        $dir = $basePath . $dir;
        if (!is_dir($dir)) {
            throw new RuntimeException(sprintf('The directory "%s" does not exist', $dir));
        }
        $this->filesystem->mirror($dir, $this->workingDir);
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
     * @param string $argumentsString
     */
    #[When('/^I run "behat(?: ((?:\\"|[^"])*))?"$/')]
    public function iRunBehat($argumentsString = ''): void
    {
        $argumentsString = strtr($argumentsString, ['\'' => '"']);

        $php = $this->phpBin;

        if ($this->errorLevel !== null) {
            $php .= ' -d error_reporting=' . $this->errorLevel;
        }

        $cmd = sprintf(
            '%s %s %s %s',
            $php,
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
     * @param string $answerString
     * @param string $argumentsString
     */
    #[When('/^I answer "([^"]+)" when running "behat(?: ((?:\\"|[^"])*))?"$/')]
    public function iRunBehatInteractively($answerString, $argumentsString): void
    {
        $this->env['SHELL_INTERACTIVE'] = true;

        $this->answerString = $answerString;

        $this->options = '--no-colors --format-settings=\'{"timer": false}\'';
        $this->iRunBehat($argumentsString);
    }

    /**
     * Runs behat command in debug mode.
     */
    #[When('/^I run behat in debug mode$/')]
    public function iRunBehatInDebugMode(): void
    {
        $this->options = '';
        $this->iRunBehat('--debug');
    }

    /**
     * Checks whether previously ran command passes|fails with provided output.
     *
     * @param 'pass'|'fail' $success
     */
    #[Then('/^it should (fail|pass) with:$/')]
    public function itShouldPassOrFailWith($success, PyStringNode $text): void
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

        if (!str_contains($this->getOutput(), (string) $this->getExpectedOutput($text))) {
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
     * @param 'pass'|'fail' $success
     */
    #[Then('/^it should (fail|pass) with no output$/')]
    public function itShouldPassOrFailWithNoOutput($success): void
    {
        Assert::assertEmpty($this->getOutput());
        $this->itShouldPassOrFail($success);
    }

    /**
     * Checks whether specified file exists and contains specified string.
     *
     * @param string       $path file path
     * @param PyStringNode $text file content
     */
    #[Then('/^"([^"]*)" file should contain:$/')]
    public function fileShouldContain($path, PyStringNode $text): void
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

    #[Then(':path file should contain exactly:')]
    public function fileShouldContainExactly(string $path, PyStringNode $text): void
    {
        $path = $this->workingDir.'/'.$path;
        Assert::assertFileExists($path);

        $fileContent = trim(file_get_contents($path));
        // Normalize the line endings in the output
        if ("\n" !== PHP_EOL) {
            $fileContent = str_replace(PHP_EOL, "\n", $fileContent);
        }

        Assert::assertEquals($text, $fileContent);
    }

    #[Then(':path file should contain text:')]
    public function fileShouldContainText(string $path, PyStringNode $text): void
    {
        $path = $this->workingDir.'/'.$path;
        Assert::assertFileExists($path);

        $fileContent = file_get_contents($path);
        $expectedText = (string) $this->getExpectedOutput($text);

        if (!str_contains($fileContent, $expectedText)) {
            throw new UnexpectedValueException(
                sprintf('File "%s" does not contain expected text.', $path)
            );
        }
    }

    /**
     * Checks whether specified content and structure of the xml is correct without worrying about layout.
     *
     * @param string       $path file path
     * @param PyStringNode $text file content
     */
    #[Then('/^(?:the\\s)?"([^"]*)" file xml should be like:$/')]
    public function fileXmlShouldBeLike($path, PyStringNode $text): void
    {
        $path = $this->workingDir . '/' . $path;
        $this->checkXmlFileContents($path, $text);
    }

    /**
     * Checks whether specified content and structure of the json is correct without worrying about layout.
     *
     * @param string       $path file path
     * @param PyStringNode $text file content
     */
    #[Then('/^(?:the\\s)?"([^"]*)" file json should be like:$/')]
    public function fileJSONShouldBeLike($path, PyStringNode $text): void
    {
        $path = $this->workingDir . '/' . $path;
        $this->checkJSONFileContents($path, $text);
    }

    #[Then('the :file file should have been removed from the working directory')]
    public function fileShouldHaveBeenRemoved($file): void
    {
        $path = $this->workingDir . '/' . $file;
        Assert::assertFileDoesNotExist($path);
    }

    private function checkXmlFileContents($path, PyStringNode $text): void
    {
        Assert::assertFileExists($path);

        $fileContent = trim(file_get_contents($path));

        $fileContent = preg_replace('/time="\d\.\d{3}"/U', 'time="-IGNORE-VALUE-"', $fileContent);

        // The placeholder is necessary because of different separators on Unix and Windows environments
        $text = str_replace('-DIRECTORY-SEPARATOR-', DIRECTORY_SEPARATOR, $text);
        // used for absolute paths
        $text = str_replace('%%WORKING_DIR%%', realpath($this->workingDir . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, $text);

        $dom = new DOMDocument();
        $dom->loadXML($text);
        $dom->formatOutput = true;

        Assert::assertEquals(trim($dom->saveXML(null, LIBXML_NOEMPTYTAG)), $fileContent);
    }

    private function checkJSONFileContents($path, PyStringNode $text): void
    {
        Assert::assertFileExists($path);

        $fileContent = trim(file_get_contents($path));

        $data = json_decode($fileContent, true, JSON_THROW_ON_ERROR);

        Assert::assertIsArray($data);

        $fileContent = preg_replace('/"time": [\d.]+/', '"time": -IGNORE-VALUE-', $fileContent);

        $text = str_replace(
            '-DIRECTORY-SEPARATOR-',
            // use the correct representation of directory separators in json for each OS
            trim(json_encode(DIRECTORY_SEPARATOR, JSON_UNESCAPED_SLASHES), '"'),
            $text
        );
        // used for absolute paths
        $text = str_replace(
            '%%WORKING_DIR%%',
            trim(json_encode(realpath($this->workingDir . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, JSON_UNESCAPED_SLASHES), '"'),
            $text
        );

        Assert::assertEquals($text, $fileContent);
    }

    /**
     * Checks whether last command output contains provided string.
     *
     * @param PyStringNode $text PyString text instance
     */
    #[Then('the output should contain:')]
    public function theOutputShouldContain(PyStringNode $text): void
    {
        if (str_contains($this->getOutput(), (string) $this->getExpectedOutput($text))) {
            return;
        }

        throw new UnexpectedValueException(
            $this->getOutputDiff($text)
        );
    }

    private function getExpectedOutput(PyStringNode $expectedText): string
    {
        $text = strtr($expectedText, [
            '\'\'\'' => '"""',
            '%%TMP_DIR%%' => sys_get_temp_dir() . DIRECTORY_SEPARATOR,
            '%%WORKING_DIR%%' => realpath($this->workingDir . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR,
            '%%DS%%' => DIRECTORY_SEPARATOR,
        ]);

        // windows path fix
        if ('/' !== DIRECTORY_SEPARATOR) {
            $text = StrictRegex::replaceCallback(
                '/[ "](features|tests)\/[^\n "]+/',
                fn ($matches): string => str_replace('/', DIRECTORY_SEPARATOR, $matches[0]),
                $text
            );
            $text = StrictRegex::replaceCallback(
                '/\<span class\="path"\>features\/[^\<]+/',
                fn ($matches): string => str_replace('/', DIRECTORY_SEPARATOR, $matches[0]),
                $text
            );
            $text = StrictRegex::replaceCallback(
                '/\+[fd] [^ ]+/',
                fn ($matches): string => str_replace('/', DIRECTORY_SEPARATOR, $matches[0]),
                $text
            );

            // error stacktrace
            $text = StrictRegex::replaceCallback(
                '/#\d+ [^:]+:/',
                fn ($matches): string => str_replace('/', DIRECTORY_SEPARATOR, $matches[0]),
                $text
            );

            // texts with absolute paths
            $text = StrictRegex::replaceCallback(
                '/\{BASE_PATH\}[^\n \<"]+/',
                fn ($matches): string => str_replace('/', DIRECTORY_SEPARATOR, $matches[0]),
                $text
            );

            // texts in editor URLs
            $text = StrictRegex::replaceCallback(
                '/open\?file[^\<"]+/',
                fn ($matches): string => str_replace('/', DIRECTORY_SEPARATOR, $matches[0]),
                $text
            );
        }

        $text = ConsoleFormatter::replaceHref($text);

        return $text;
    }

    /**
     * Checks whether previously ran command failed|passed.
     *
     * @param 'pass'|'fail' $success
     */
    #[Then('/^it should (fail|pass)$/')]
    public function itShouldPassOrFail($success): void
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
     * @param string $xmlFile
     * @param string $schemaPath relative to features/bootstrap/schema
     */
    #[Then('/^the file "([^"]+)" should be a valid document according to "([^"]+)"$/')]
    public function xmlShouldBeValid($xmlFile, $schemaPath): void
    {
        $path = $this->workingDir . '/' . $xmlFile;
        $this->checkXmlIsValid($path, $schemaPath);
    }

    #[Then('the file :jsonFile should be a valid document according to the json schema :schemaFile')]
    public function theFileShouldBeAValidDocumentAccordingToTheJsonSchema($jsonFile, $schemaFile): void
    {
        $json = json_decode(file_get_contents($this->workingDir . '/' . $jsonFile));
        $schema = file_get_contents(__DIR__ . '/../../resources/' . $schemaFile);

        $validator = new Validator();

        $result = $validator->validate($json, $schema);

        if (!$result->isValid()) {
            throw new UnexpectedValueException('JSON is not valid according to schema');
        }
    }

    #[Then('the :file file should not exist')]
    public function theFileShouldNotExist($file): void
    {
        $path = $this->workingDir . '/' . $file;
        if (is_file($path)) {
            throw new Exception("File $file exists");
        }
    }

    #[Given('I set the php error_reporting option for the behat command to :level')]
    public function iSetThePhpErrorReportingOptionForTheBehatCommandTo($level): void
    {
        $this->errorLevel = match ($level) {
            'none' => 0,
            'ignore deprecations' => E_ALL & ~E_DEPRECATED,
            'all' => E_ALL,
        };
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
            realpath(dirname(__DIR__, 2)) . DIRECTORY_SEPARATOR,
            '{BASE_PATH}',
            $output
        );

        // Replace wrong warning message of HHVM
        $output = str_replace('Notice: Undefined index: ', 'Notice: Undefined offset: ', $output);

        return trim((string) preg_replace('/ +$/m', '', $output));
    }

    private function createFileInWorkingDir(string $filename, string $content): void
    {
        $this->filesystem->dumpFile($this->workingDir . DIRECTORY_SEPARATOR . $filename, $content);
    }

    private function moveToNewPath($path): void
    {
        $newWorkingDir = $this->workingDir . '/' . $path;
        $this->filesystem->mkdir($newWorkingDir);

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

    private function addBehatOptions(TableNode $table): void
    {
        $rows = $table->getHash();
        foreach ($rows as $row) {
            $option = $row['option'];
            $value = $row['value'];
            if ($value !== '') {
                if (str_starts_with($value, '{BASE_PATH}')) {
                    $basePath = realpath($this->workingDir) . DIRECTORY_SEPARATOR;
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

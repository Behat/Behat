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
use Behat\Hook\AfterScenario;
use Behat\Hook\AfterSuite;
use Behat\Hook\BeforeScenario;
use Behat\Hook\BeforeSuite;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\Assert;
use React\ChildProcess\Process as ReactProcess;
use React\EventLoop\Loop;
use React\Http\Browser;
use React\Promise\Deferred;
use React\Promise\Timer\TimeoutException;
use React\Stream\ReadableStreamInterface;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\DiffOnlyOutputBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

use function React\Async\await;
use function React\Promise\Timer\sleep;
use function React\Promise\Timer\timeout;

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

    private ?ReactProcess $mcpReactProcess = null;

    private string $mcpServerErrorOutput = '';

    private ?int $errorLevel = null;

    private ?int $mcpHttpPort = null;

    private ?string $mcpHttpHost = null;

    private ?string $mcpSessionId = null;

    private array $mcpHttpResponses = [];

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

    #[AfterScenario]
    public function stopMcpServer(): void
    {
        if (!$this->mcpReactProcess instanceof ReactProcess) {
            return;
        }

        if ($this->mcpReactProcess->stdout instanceof ReadableStreamInterface) {
            $this->mcpReactProcess->stdout->close();
        }
        if ($this->mcpReactProcess->stderr instanceof ReadableStreamInterface) {
            $this->mcpReactProcess->stderr->close();
        }

        exec('pkill -9 -f "behat --mcp-server"');

        $this->mcpReactProcess = null;
        $this->mcpHttpPort = null;
        $this->mcpHttpHost = null;
        $this->mcpHttpResponses = [];
        $this->mcpSessionId = null;
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

        $fileContent = $this->normalizePhpFileLineNumbers((string) $fileContent);

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

        $fileContent = $this->normalizePhpFileLineNumbers((string) $fileContent);

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

    #[Given('I start the MCP server')]
    public function iStartTheMcpServer(): void
    {
        $command = $this->phpBin . ' ' . BEHAT_BIN_PATH . ' --mcp-server';

        $this->mcpReactProcess = new ReactProcess($command, $this->workingDir);
        $this->mcpReactProcess->start(Loop::get());

        $this->mcpServerErrorOutput = '';
        $this->mcpReactProcess->stderr->on('data', function ($chunk) {
            $this->mcpServerErrorOutput .= $chunk;
        });
    }

    #[When('I send an MCP initialize request')]
    public function iSendAnMcpInitializeRequest(): void
    {
        $this->sendMcpRequest('init-1', 'initialize', [
            'protocolVersion' => '2025-03-26',
            'clientInfo' => ['name' => 'BehatTestClient', 'version' => '1.0'],
            'capabilities' => [],
        ]);
    }

    #[Then('I should receive a successful MCP initialize response')]
    public function iShouldReceiveASuccessfulMcpInitializeResponse(): void
    {
        $response = $this->readMcpResponse('init-1');

        Assert::assertArrayHasKey('result', $response);
        Assert::assertArrayNotHasKey('error', $response);
        Assert::assertEquals('init-1', $response['id']);
        Assert::assertArrayHasKey('protocolVersion', $response['result']);
        Assert::assertArrayHasKey('serverInfo', $response['result']);

        $this->sendMcpNotification('notifications/initialized');
    }

    #[When('I call the MCP tool :toolName')]
    public function iCallTheMcpTool(string $toolName): void
    {
        $this->sendMcpRequest('tool-call-1', 'tools/call', [
            'name' => $toolName,
            'arguments' => [],
        ]);
    }

    #[When('I call the MCP tool :toolName with config :config')]
    public function iCallTheMcpToolWithConfig(string $toolName, string $config): void
    {
        $this->sendMcpRequest('tool-call-1', 'tools/call', [
            'name' => $toolName,
            'arguments' => ['config' => $config],
        ]);
    }

    #[When('I call the MCP tool :toolName with profile :profile')]
    public function iCallTheMcpToolWithProfile(string $toolName, string $profile): void
    {
        $this->sendMcpRequest('tool-call-1', 'tools/call', [
            'name' => $toolName,
            'arguments' => ['profile' => $profile],
        ]);
    }

    #[When('I call the MCP tool :toolName with suite :suite')]
    public function iCallTheMcpToolWithSuite(string $toolName, string $suite): void
    {
        $this->sendMcpRequest('tool-call-1', 'tools/call', [
            'name' => $toolName,
            'arguments' => ['suite' => $suite],
        ]);
    }

    #[When('I call the MCP tool :toolName with paths :paths')]
    public function iCallTheMcpToolWithPaths(string $toolName, string $paths): void
    {
        $this->sendMcpRequest('tool-call-1', 'tools/call', [
            'name' => $toolName,
            'arguments' => ['paths' => explode(',', $paths)],
        ]);
    }

    #[When('I call the MCP tool :toolName with additional options :options')]
    public function iCallTheMcpToolWithAdditionalOptions(string $toolName, string $options): void
    {
        $additionalOptions = [];
        foreach (explode(',', $options) as $option) {
            [$key, $value] = explode('=', $option, 2);
            $additionalOptions[$key] = $value === 'true' ? true : ($value === 'false' ? false : $value);
        }
        $this->sendMcpRequest('tool-call-1', 'tools/call', [
            'name' => $toolName,
            'arguments' => ['additionalOptions' => $additionalOptions],
        ]);
    }

    #[Then('I should receive a successful tool response with :tests tests and :failures failures')]
    public function iShouldReceiveASuccessfulToolResponseWithTestsAndFailures(int $tests, int $failures): void
    {
        $response = $this->readMcpResponse('tool-call-1');

        Assert::assertArrayHasKey('result', $response);
        Assert::assertArrayNotHasKey('error', $response);
        Assert::assertEquals('tool-call-1', $response['id']);

        $toolResult = json_decode((string) $response['result']['content'][0]['text'], true);

        Assert::assertEquals($tests, $toolResult['tests']);
        Assert::assertEquals($failures, $toolResult['failed']);
    }

    #[Then('I should receive a successful tool response with :tests tests and :skipped skipped')]
    public function iShouldReceiveASuccessfulDryRunToolResponseWithTests(int $tests, int $skipped): void
    {
        $response = $this->readMcpResponse('tool-call-1');

        Assert::assertArrayHasKey('result', $response);
        Assert::assertArrayNotHasKey('error', $response);
        Assert::assertEquals('tool-call-1', $response['id']);

        $toolResult = json_decode((string) $response['result']['content'][0]['text'], true);

        Assert::assertEquals($tests, $toolResult['tests']);
        Assert::assertEquals($skipped, $toolResult['skipped']);
    }

    private function sendMcpNotification(string $method, array $params = []): void
    {
        $notification = json_encode([
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
        ]);
        $this->mcpReactProcess->stdin->write($notification . "\n");
    }

    private function sendMcpRequest(string $requestId, string $method, array $params = []): void
    {
        $request = json_encode([
            'jsonrpc' => '2.0',
            'id' => $requestId,
            'method' => $method,
            'params' => $params,
        ]);
        $this->mcpReactProcess->stdin->write($request . "\n");
    }

    private function readMcpResponse(string $expectedRequestId): array
    {
        $loop = Loop::get();
        $deferred = new Deferred();
        $buffer = '';
        $timeoutSeconds = 5;

        $dataListener = function ($chunk) use (&$buffer, $deferred, $expectedRequestId, &$dataListener) {
            $buffer .= $chunk;
            if (str_contains($buffer, "\n")) {
                $lines = explode("\n", $buffer);
                $buffer = array_pop($lines);

                foreach ($lines as $line) {
                    if (in_array(trim($line), ['', '0'], true)) {
                        continue;
                    }
                    $response = json_decode(trim($line), true);
                    if (is_array($response) && array_key_exists('id', $response) && $response['id'] === $expectedRequestId) {
                        $this->mcpReactProcess->stdout->removeListener('data', $dataListener);
                        $deferred->resolve($response);

                        return;
                    }
                }
            }
        };

        $this->mcpReactProcess->stdout->on('data', $dataListener);

        $promise = timeout($deferred->promise(), $timeoutSeconds, $loop);

        try {
            return await($promise);
        } catch (TimeoutException) {
            $this->mcpReactProcess->stdout->removeListener('data', $dataListener);
            throw new RuntimeException("Timeout waiting for MCP response with ID '{$expectedRequestId}'");
        }
    }

    #[Given('I start the MCP server with HTTP transport')]
    public function iStartTheMcpServerWithHttpTransport(): void
    {
        $this->mcpHttpHost = '127.0.0.1';
        $this->mcpHttpPort = 19876;

        $command = sprintf(
            '%s %s --mcp-server --mcp-transport=http --mcp-host=%s --mcp-port=%d',
            $this->phpBin,
            BEHAT_BIN_PATH,
            $this->mcpHttpHost,
            $this->mcpHttpPort
        );

        $this->mcpReactProcess = new ReactProcess($command, $this->workingDir);
        $this->mcpReactProcess->start(Loop::get());

        $this->mcpServerErrorOutput = '';
        $this->mcpReactProcess->stderr->on('data', function ($chunk) {
            $this->mcpServerErrorOutput .= $chunk;
        });

        await(sleep(0.5));
    }

    #[When('I send an HTTP MCP initialize request')]
    public function iSendAnHttpMcpInitializeRequest(): void
    {
        $this->sendHttpMcpRequest('init-1', 'initialize', [
            'protocolVersion' => '2025-03-26',
            'clientInfo' => ['name' => 'BehatTestClient', 'version' => '1.0'],
            'capabilities' => [],
        ]);
    }

    #[Then('I should receive a successful HTTP MCP initialize response')]
    public function iShouldReceiveASuccessfulHttpMcpInitializeResponse(): void
    {
        $response = $this->mcpHttpResponses['init-1'] ?? null;

        Assert::assertNotNull($response);
        Assert::assertArrayHasKey('result', $response);
        Assert::assertArrayNotHasKey('error', $response);
        Assert::assertEquals('init-1', $response['id']);
        Assert::assertArrayHasKey('protocolVersion', $response['result']);
        Assert::assertArrayHasKey('serverInfo', $response['result']);

        $this->sendHttpMcpNotification('notifications/initialized');
    }

    #[When('I call the HTTP MCP tool :toolName')]
    public function iCallTheHttpMcpTool(string $toolName): void
    {
        $this->sendHttpMcpRequest('tool-call-1', 'tools/call', [
            'name' => $toolName,
            'arguments' => [],
        ]);
    }

    #[Then('I should receive a successful HTTP tool response with :tests tests and :failures failures')]
    public function iShouldReceiveASuccessfulHttpToolResponse(int $tests, int $failures): void
    {
        $response = $this->mcpHttpResponses['tool-call-1'] ?? null;

        Assert::assertNotNull($response);
        Assert::assertArrayHasKey('result', $response);
        Assert::assertArrayNotHasKey('error', $response);
        Assert::assertEquals('tool-call-1', $response['id']);

        $toolResult = json_decode((string) $response['result']['content'][0]['text'], true);

        Assert::assertEquals($tests, $toolResult['tests']);
        Assert::assertEquals($failures, $toolResult['failed']);
    }

    private function sendHttpMcpRequest(string $requestId, string $method, array $params = []): void
    {
        $browser = new Browser();
        $url = sprintf('http://%s:%d/mcp', $this->mcpHttpHost, $this->mcpHttpPort);

        $payload = json_encode([
            'jsonrpc' => '2.0',
            'id' => $requestId,
            'method' => $method,
            'params' => $params,
        ]);

        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];
        if ($this->mcpSessionId !== null && $method !== 'initialize') {
            $headers['Mcp-Session-Id'] = $this->mcpSessionId;
        }

        $response = await($browser->post($url, $headers, $payload));

        if ($method === 'initialize' && $response->hasHeader('Mcp-Session-Id')) {
            $this->mcpSessionId = $response->getHeaderLine('Mcp-Session-Id');
        }

        $body = (string) $response->getBody();
        $this->mcpHttpResponses[$requestId] = json_decode($body, true);
    }

    private function sendHttpMcpNotification(string $method, array $params = []): void
    {
        $browser = new Browser();
        $url = sprintf('http://%s:%d/mcp', $this->mcpHttpHost, $this->mcpHttpPort);

        $payload = json_encode([
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
        ]);

        $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json', 'Mcp-Session-Id' => $this->mcpSessionId];

        await($browser->post($url, $headers, $payload));
    }

    private function checkXmlIsValid(string $xmlFile, string $schemaPath): void
    {
        $dom = new DOMDocument();
        $dom->load($xmlFile);

        $dom->schemaValidate(__DIR__ . '/schema/' . $schemaPath);
    }

    private function getExitCode(): ?int
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

        $output = $this->normalizePhpFileLineNumbers($output);

        return trim((string) preg_replace('/ +$/m', '', (string) $output));
    }

    /**
     * Normalizes PHP file line numbers to XX to avoid fragile tests.
     */
    private function normalizePhpFileLineNumbers(string $content): string
    {
        $content = preg_replace('/\.php line \d+/', '.php line XX', $content);
        $content = preg_replace('/\.php:\d+/', '.php:XX', (string) $content);
        $content = preg_replace('/\.php\(\d+\)/', '.php(XX)', (string) $content);
        $content = preg_replace('/\.php&line=\d+/', '.php&line=XX', (string) $content);

        return (string) $content;
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

<?php

declare(strict_types=1);

namespace Behat\Testwork\Mcp;

use Behat\Behat\ApplicationFactory;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class McpTestRunner
{
    private const PROTECTED_OPTIONS = ['--format', '--out', '-f', '-o'];

    #[McpTool(
        name: 'run-behat-tests',
        description: 'Run all Behat BDD tests in the current project using the default config file. Use the additional parameters if you want to use a different config file or restrict the run to a specific profile, suite, path or scenario',
        annotations: new ToolAnnotations(
            title: 'Run Behat Tests',
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false
        )
    )]
    public function runBehatTests(
        #[Schema(description: 'Path to a Behat configuration file (optional)')]
        ?string $config = null,
        #[Schema(description: 'Name of a profile to use (optional)')]
        ?string $profile = null,
        #[Schema(description: 'Name of a suite to use (optional)')]
        ?string $suite = null,
        #[Schema(description: 'List of paths to execute (optional)', items: ['type' => 'string'])]
        ?array $paths = null,
        #[Schema(description: 'Additional command-line options as key-value pairs (optional)', type: 'object', additionalProperties: true)]
        ?array $additionalOptions = null,
    ): array {
        $outputFile = tempnam(sys_get_temp_dir(), 'behat_output_');

        $factory = new ApplicationFactory();
        $application = $factory->createApplication();
        $application->setAutoExit(false);

        $inputArgs = [
            '--no-colors' => true,
            '--format' => ['json'],
            '--out' => [$outputFile],
        ];

        if ($config !== null) {
            $inputArgs['--config'] = $config;
        }

        if ($profile !== null) {
            $inputArgs['--profile'] = $profile;
        }

        if ($suite !== null) {
            $inputArgs['--suite'] = $suite;
        }

        if ($additionalOptions !== null) {
            foreach ($additionalOptions as $option => $value) {
                if (!in_array($option, self::PROTECTED_OPTIONS, true)) {
                    $inputArgs[$option] = $value;
                }
            }
        }

        if ($paths !== null) {
            $inputArgs['paths'] = $paths;
        }

        $input = new ArrayInput($inputArgs);
        $output = new BufferedOutput();

        $application->run($input, $output);

        $result = file_get_contents($outputFile);
        unlink($outputFile);

        return json_decode($result, true);
    }
}

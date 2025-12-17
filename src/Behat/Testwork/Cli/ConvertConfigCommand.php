<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Cli;

use Behat\Behat\Util\StrictRegex;
use Behat\Config\Config;
use Behat\Config\Converter\ConfigConverterTools;
use Behat\Config\Converter\CustomPrettyPrinter;
use Behat\Config\Converter\UsedClassesCollector;
use Behat\Testwork\ServiceContainer\Configuration\ConfigurationLoader;
use Behat\Testwork\ServiceContainer\Exception\ConfigurationLoadingException;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

final class ConvertConfigCommand extends BaseCommand
{
    private OutputInterface $output;

    public function __construct(
        private readonly ConfigurationLoader $configurationLoader,
    ) {
        parent::__construct('convert-config');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $configPath = $this->configurationLoader->getConfigurationFilePath();
        if (str_ends_with((string) $configPath, '.php')) {
            throw new ConfigurationLoadingException(sprintf('Configuration file `%s` is already in PHP format', $configPath));
        }

        $this->output->writeln(['', 'Starting conversion']);
        $this->convertFile($configPath);
        $this->output->writeln(['Conversion finished', '']);

        return 0;
    }

    private function convertFile(string $filePath): string
    {
        $this->output->writeln('Converting configuration file: ' . $filePath);

        $yamlConfig = (array) Yaml::parse(file_get_contents($filePath));

        if (isset($yamlConfig[Config::IMPORTS_SETTING])) {
            $imports = $yamlConfig[Config::IMPORTS_SETTING];
            $basePath = rtrim(dirname($filePath), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

            $newImports = [];
            foreach ($imports as $import) {
                $import = $basePath . $import;
                $newImports[] = substr($this->convertFile($import), strlen($basePath));
            }
            $yamlConfig[Config::IMPORTS_SETTING] = $newImports;
        }

        $config = new Config($yamlConfig);

        $expr = $config->toPhpExpr();

        $configStms = [new Return_($expr)];

        $traverser = new NodeTraverser();
        $usedClasssesCollector = new UsedClassesCollector();
        $traverser->addVisitor($usedClasssesCollector);
        $configStms = $traverser->traverse($configStms);

        $useStmts = [];

        $usedClasses = $usedClasssesCollector->getUsedClasses();
        sort($usedClasses);
        foreach ($usedClasses as $usedClass) {
            $useStmts[] = ConfigConverterTools::createUseStatement($usedClass);
        }
        if ($useStmts !== []) {
            $useStmts[] = new Nop();
        }

        $stmts = [...$useStmts, ...$configStms, new Nop()];

        // Find the name of the output file
        $outputFileName = $filePath;
        // First, handle cases where `.dist` is part of the extension
        $outputFileName = StrictRegex::replace('/(\.ya?ml)\.dist$|\.dist\.(ya?ml)$/', '.dist.php', $outputFileName);
        // Then, handle regular `.yaml` or `.yml` files
        $outputFileName = StrictRegex::replace('/\.(ya?ml)$/', '.php', $outputFileName);

        $printer = new CustomPrettyPrinter();

        file_put_contents($outputFileName, $printer->prettyPrintFile($stmts));

        unlink($filePath);

        return $outputFileName;
    }
}

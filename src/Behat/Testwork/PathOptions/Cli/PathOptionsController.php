<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\PathOptions\Cli;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\PathOptions\Printer\ConfigurablePathPrinter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Configures the printing of paths in the output.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PathOptionsController implements Controller
{
    public function __construct(
        private readonly ConfigurablePathPrinter $configurablePathPrinter,
    ) {
    }

    public function configure(Command $command)
    {
        $command
            ->addOption(
                '--print-absolute-paths', null, InputOption::VALUE_NONE,
                'Print absolute paths in output'
            )
            ->addOption(
                '--editor-url', null, InputOption::VALUE_REQUIRED,
                'URL template for opening files in an editor'
            )
            ->addOption(
                '--remove-prefix', null, InputOption::VALUE_REQUIRED,
                'Comma-separated list of prefixes to remove from paths'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $printAbsolutePaths = $input->getOption('print-absolute-paths');
        $editorUrl = $input->getOption('editor-url');
        $removePrefix = $input->getOption('remove-prefix');

        // Parse comma-separated list into an array
        $removePrefixArray = [];
        if ($removePrefix !== null) {
            assert(is_string($removePrefix), '--remove-prefix option should be string or null');
            $removePrefixArray = explode(',', $removePrefix);
        }

        $this->configurePrintPaths($printAbsolutePaths, $editorUrl, $removePrefixArray);

        return null;
    }

    /**
     * @param string[] $removePrefix
     */
    private function configurePrintPaths(bool $printAbsolutePaths, ?string $editorUrl, array $removePrefix = []): void
    {
        if ($printAbsolutePaths) {
            $this->configurablePathPrinter->setPrintAbsolutePaths($printAbsolutePaths);
        }

        if ($editorUrl !== null) {
            $this->configurablePathPrinter->setEditorUrl($editorUrl);
        }

        if ($removePrefix !== []) {
            $this->configurablePathPrinter->setRemovePrefix($removePrefix);
        }
    }
}

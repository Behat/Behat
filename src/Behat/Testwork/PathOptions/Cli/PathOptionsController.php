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
        private ConfigurablePathPrinter $configurablePathPrinter,
    ) {
    }

    public function configure(Command $command)
    {
        $command
            ->addOption(
                '--print-absolute-paths', null, InputOption::VALUE_NONE,
                'Print absolute paths in output'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $printAbsolutePaths = $input->getOption('print-absolute-paths');

        $this->configurePrintPaths($printAbsolutePaths);

        return null;
    }

    private function configurePrintPaths(bool $printAbsolutePaths): void
    {
        if ($printAbsolutePaths) {
            $this->configurablePathPrinter->setPrintAbsolutePaths($printAbsolutePaths);
        }
    }
}

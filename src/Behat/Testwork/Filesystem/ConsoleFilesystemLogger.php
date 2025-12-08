<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Filesystem;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Logs filesystem operations to the console.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ConsoleFilesystemLogger implements FilesystemLogger
{
    /**
     * Initializes logger.
     *
     * @param string          $basePath
     */
    public function __construct(
        private $basePath,
        private readonly OutputInterface $output,
    ) {
    }

    public function directoryCreated($path, $reason): void
    {
        $this->output->writeln(
            sprintf(
                '<info>+d</info> %s - %s',
                str_replace($this->basePath . DIRECTORY_SEPARATOR, '', realpath($path)),
                $reason
            )
        );
    }

    public function fileCreated($path, $reason): void
    {
        $this->output->writeln(
            sprintf(
                '<info>+f</info> %s - %s',
                str_replace($this->basePath . DIRECTORY_SEPARATOR, '', realpath($path)),
                $reason
            )
        );
    }

    public function fileUpdated($path, $reason): void
    {
        $this->output->writeln(
            sprintf(
                '<info>u</info> %s - %s',
                str_replace($this->basePath . DIRECTORY_SEPARATOR, '', realpath($path)),
                $reason
            )
        );
    }
}

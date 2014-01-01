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
 * Testwork console filesystem operations logger.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ConsoleFilesystemLogger implements FilesystemLogger
{
    /**
     * @var string
     */
    private $basePath;
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Initializes logger.
     *
     * @param string          $basePath
     * @param OutputInterface $output
     */
    public function __construct($basePath, OutputInterface $output)
    {
        $this->basePath = $basePath;
        $this->output = $output;
    }

    /**
     * Logs directory creation.
     *
     * @param string $path
     * @param string $reason
     */
    public function directoryCreated($path, $reason)
    {
        $this->output->writeln(
            sprintf(
                '<info>+d</info> %s <comment>- %s</comment>',
                str_replace($this->basePath . DIRECTORY_SEPARATOR, '', realpath($path)),
                $reason
            )
        );
    }

    /**
     * Logs file creation.
     *
     * @param string $path
     * @param string $reason
     */
    public function fileCreated($path, $reason)
    {
        $this->output->writeln(
            sprintf(
                '<info>+f</info> %s <comment>- %s</comment>',
                str_replace($this->basePath . DIRECTORY_SEPARATOR, '', realpath($path)),
                $reason
            )
        );
    }

    /**
     * Logs file update.
     *
     * @param string $path
     * @param string $reason
     */
    public function fileUpdated($path, $reason)
    {
        $this->output->writeln(
            sprintf(
                '<info>u</info> %s <comment>- %s</comment>',
                str_replace($this->basePath . DIRECTORY_SEPARATOR, '', realpath($path)),
                $reason
            )
        );
    }
}

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
     * {@inheritdoc}
     */
    public function directoryCreated($path, $reason)
    {
        $this->output->writeln(
            sprintf(
                '<info>+d</info> %s - %s',
                str_replace($this->basePath . DIRECTORY_SEPARATOR, '', realpath($path)),
                $reason
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function fileCreated($path, $reason)
    {
        $this->output->writeln(
            sprintf(
                '<info>+f</info> %s - %s',
                str_replace($this->basePath . DIRECTORY_SEPARATOR, '', realpath($path)),
                $reason
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function fileUpdated($path, $reason)
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

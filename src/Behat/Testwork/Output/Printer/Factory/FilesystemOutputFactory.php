<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Printer\Factory;

use Behat\Testwork\Output\Exception\BadOutputPathException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Creates an output stream for the filesystem.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class FilesystemOutputFactory extends OutputFactory
{
    private $fileName;

    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * Configure output stream parameters.
     *
     * @param OutputInterface $output
     */
    protected function configureOutputStream(OutputInterface $output)
    {
        $verbosity = $this->getOutputVerbosity() ? OutputInterface::VERBOSITY_VERBOSE : OutputInterface::VERBOSITY_NORMAL;
        $output->setVerbosity($verbosity);
    }

    /**
     * {@inheritDoc}
     */
    public function createOutput($stream = null)
    {
        if (is_file($this->getOutputPath())) {
            throw new BadOutputPathException(
                'Directory expected for the `output_path` option, but a filename was given.',
                $this->getOutputPath()
            );
        } elseif (!is_dir($this->getOutputPath())) {
            mkdir($this->getOutputPath(), 0777, true);
        }

        if (null === $this->fileName) {
            throw new \LogicException('Unable to create file, no file name specified');
        }

        $filePath = $this->getOutputPath().'/'.$this->fileName;

        $stream = new StreamOutput(
            fopen($filePath, 'w'),
            StreamOutput::VERBOSITY_NORMAL,
            false // a file is never decorated
        );
        $this->configureOutputStream($stream);

        return $stream;
    }
}

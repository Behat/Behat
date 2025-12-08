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
use Behat\Testwork\Output\Exception\MissingOutputPathException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

final class FileOutputFactory extends OutputFactory
{
    public function createOutput($stream = null): OutputInterface
    {
        if ($this->getOutputPath() === null) {
            throw new MissingOutputPathException(
                'The `output_path` option must be specified.',
            );
        }

        $fileName = $this->getOutputPath();

        if (is_dir($fileName)) {
            throw new BadOutputPathException(
                'A file name expected for the `output_path` option, but a directory was given.',
                $this->getOutputPath()
            );
        }

        $dir = dirname($fileName);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $file = fopen($fileName, 'w');
        if ($file === false) {
            throw new BadOutputPathException(
                'The file named in the `output_path` option could not be opened.',
                $this->getOutputPath()
            );
        }

        $stream = new StreamOutput(
            $file,
            StreamOutput::VERBOSITY_NORMAL,
            false // a file is never decorated
        );

        return $stream;
    }
}

<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Printer\Factory;

use Behat\Testwork\Output\Exception\MissingOutputPathException;
use Symfony\Component\Console\Output\StreamOutput;

final class FileOutputFactory extends OutputFactory
{
    public function createOutput($stream = null)
    {
        if ($this->getOutputPath() === null) {
            throw new MissingOutputPathException(
                'The `output_path` option must be specified.',
            );
        }

        $fileName = $this->getOutputPath();

        $stream = new StreamOutput(
            fopen($fileName, 'w'),
            StreamOutput::VERBOSITY_NORMAL,
            false // a file is never decorated
        );

        return $stream;
    }
}

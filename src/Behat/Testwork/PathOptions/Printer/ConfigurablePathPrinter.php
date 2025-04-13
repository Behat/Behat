<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\PathOptions\Printer;

final class ConfigurablePathPrinter
{
    private string $basePath;

    public function __construct(
        string $basePath,
        private bool $printAbsolutePaths,
    ) {
        $realBasePath = realpath($basePath);

        if ($realBasePath) {
            $basePath = $realBasePath;
        }

        $this->basePath = $basePath;
    }

    public function setPrintAbsolutePaths(bool $printAbsolutePaths): void
    {
        $this->printAbsolutePaths = $printAbsolutePaths;
    }

    /**
     * Conditionally transforms paths to relative.
     */
    public function processPathsInText(string $text): string
    {
        if ($this->printAbsolutePaths === true) {
            return $text;
        }

        return str_replace($this->basePath . DIRECTORY_SEPARATOR, '', $text);
    }
}

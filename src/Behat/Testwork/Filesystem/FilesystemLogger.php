<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Filesystem;

/**
 * Logs filesystem operations.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface FilesystemLogger
{
    /**
     * Logs directory creation.
     */
    public function directoryCreated(string $path, string $reason): void;

    /**
     * Logs file creation.
     */
    public function fileCreated(string $path, string $reason): void;

    /**
     * Logs file update.
     */
    public function fileUpdated(string $path, string $reason): void;
}

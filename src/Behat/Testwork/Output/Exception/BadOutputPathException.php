<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Exception;

use InvalidArgumentException;

/**
 * Represents an exception thrown because user provided bad output path.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BadOutputPathException extends InvalidArgumentException implements PrinterException
{
    /**
     * Initializes exception.
     */
    public function __construct(
        string $message,
        private readonly string $path,
    ) {
        parent::__construct($message);
    }

    /**
     * Returns exception causing path.
     */
    public function getPath(): string
    {
        return $this->path;
    }
}

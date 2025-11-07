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
 * Represents an exception thrown because user did not provide an output path.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class MissingOutputPathException extends InvalidArgumentException implements PrinterException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}

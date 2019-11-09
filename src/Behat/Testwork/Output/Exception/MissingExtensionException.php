<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Exception;

use RuntimeException;

/**
 * Represents an exception thrown when a required extension is missing.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class MissingExtensionException extends RuntimeException implements PrinterException
{
}

<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\ServiceContainer\Exception;

use RuntimeException;

/**
 * Represents an exception thrown during processing phase.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class ProcessingException extends RuntimeException implements ServiceContainerException
{
}

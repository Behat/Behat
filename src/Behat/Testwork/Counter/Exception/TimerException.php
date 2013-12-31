<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Counter\Exception;

use Behat\Testwork\Exception\TestworkException;
use RuntimeException;

/**
 * Testwork timer exception.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TimerException extends RuntimeException implements TestworkException
{
}

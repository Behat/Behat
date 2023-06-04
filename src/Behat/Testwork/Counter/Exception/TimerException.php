<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Counter\Exception;

use Behat\Testwork\Exception\TestworkException;

/**
 * Represents exception caused by timer handling.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class TimerException extends \LogicException implements TestworkException
{
}
